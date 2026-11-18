<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Notifier;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::latest()->paginate(20);
        $stats = [
            'unread' => AdminNotification::whereNull('read_at')->count(),
            'sent' => NotificationLog::where('status', 'sent')->count(),
            'failed' => NotificationLog::where('status', 'failed')->count(),
            'cost' => NotificationLog::sum('cost'),
        ];
        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    public function markAllRead()
    {
        AdminNotification::whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(AdminNotification $notification)
    {
        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        return redirect($notification->link ?: route('admin.dashboard'));
    }

    public function clear()
    {
        AdminNotification::truncate();
        return back()->with('success', 'Notifications cleared.');
    }

    /* ---------------- Send ---------------- */

    public function sendForm(Request $request)
    {
        $customers = User::where('role', 'customer')->orderBy('name')->take(500)->get();
        $templates = NotificationTemplate::where('is_active', true)->orderBy('name')->get();
        return view('admin.notifications.send', compact('customers', 'templates'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'audience' => 'required|in:all,selected,promotions,new_products',
            'selected_customers' => 'required_if:audience,selected|array',
            'selected_customers.*' => 'exists:users,id',
            'channel' => 'required|in:email,sms,both',
            'type' => 'required|in:custom,promotion,new_product',
            'subject' => 'required_if:type,custom,promotion|string|max:255',
            'body' => 'required|string',
        ]);

        $subject = $data['subject'] ?? ('New from ' . config('app.name'));

        [$users, $emailPref, $smsPref] = $this->audienceMap($data);

        $sent = 0;
        $skipped = 0;
        foreach ($users as $user) {
            $prefs = NotificationPreference::for($user);
            if (! $emailPref($prefs) && ! $smsPref($prefs)) {
                $skipped++;
                continue;
            }
            $key = 'blast:' . $data['type'] . ':' . $user->id . ':' . md5($data['body'] . $subject);
            if (in_array($data['channel'], ['email', 'both']) && $emailPref($prefs)) {
                if (Notifier::email($user, $data['type'], $subject, $data['body'], $key . ':email')) {
                    $sent++;
                } else {
                    $skipped++;
                }
            }
            if (in_array($data['channel'], ['sms', 'both']) && $smsPref($prefs)) {
                if (Notifier::sms($user, $data['type'], $subject . "\n" . $data['body'], $key . ':sms')) {
                    $sent++;
                } else {
                    $skipped++;
                }
            }
        }

        ActivityLogger::log('notifications.send', $data['type'], "Sent {$sent}, skipped {$skipped}");

        return redirect()->route('admin.notifications.index')->with('success', "Notification sent to {$sent} customers ({$skipped} skipped due to preferences).");
    }

    private function audienceMap(array $data): array
    {
        switch ($data['audience']) {
            case 'selected':
                return [User::whereIn('id', $data['selected_customers'])->where('role', 'customer')->get(),
                    fn ($p) => true,
                    fn ($p) => (bool) $p->email_order_updates];
            case 'promotions':
                return [User::where('role', 'customer')->get(),
                    fn ($p) => (bool) $p->email_promotions,
                    fn ($p) => (bool) $p->sms_promotions];
            case 'new_products':
                return [User::where('role', 'customer')->get(),
                    fn ($p) => (bool) $p->email_new_products,
                    fn ($p) => (bool) $p->sms_new_products];
            default:
                return [User::where('role', 'customer')->get(),
                    fn ($p) => (bool) $p->email_order_updates,
                    fn ($p) => (bool) $p->sms_order_updates];
        }
    }

    /* ---------------- History ---------------- */

    public function history(Request $request)
    {
        $query = NotificationLog::with('user')->latest();
        if ($channel = $request->string('channel')->toString()) {
            $query->where('channel', $channel);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('recipient', 'like', "%{$q}%")->orWhere('subject', 'like', "%{$q}%"));
        }
        $logs = $query->paginate(20)->withQueryString();
        return view('admin.notifications.history', compact('logs'));
    }

    public function retry(NotificationLog $log)
    {
        $ok = Notifier::retry($log);
        return back()->with($ok ? 'success' : 'error', $ok ? 'Notification re-sent.' : 'Retry failed — see error in the history.');
    }

    /* ---------------- Templates ---------------- */

    public function templates()
    {
        $templates = NotificationTemplate::orderBy('name')->get();
        return view('admin.notifications.templates.index', compact('templates'));
    }

    public function templatesCreate()
    {
        return view('admin.notifications.templates.form', ['template' => new NotificationTemplate(['channel' => 'both', 'is_active' => true])]);
    }

    public function templatesStore(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:50|alpha_dash|unique:notification_templates,key',
            'name' => 'required|string|max:255',
            'channel' => 'required|in:email,sms,both',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        NotificationTemplate::create($data);
        ActivityLogger::log('template.create', $data['key'], 'Template created');
        return redirect()->route('admin.notifications.templates')->with('success', 'Template created.');
    }

    public function templatesEdit(NotificationTemplate $template)
    {
        return view('admin.notifications.templates.form', compact('template'));
    }

    public function templatesUpdate(Request $request, NotificationTemplate $template)
    {
        $data = $request->validate([
            'key' => 'required|string|max:50|alpha_dash|unique:notification_templates,key,' . $template->id,
            'name' => 'required|string|max:255',
            'channel' => 'required|in:email,sms,both',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $template->update($data);
        return redirect()->route('admin.notifications.templates')->with('success', 'Template updated.');
    }

    public function templatesDestroy(NotificationTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }

    public function activity()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(25);
        return view('admin.activity.index', compact('logs'));
    }
}
