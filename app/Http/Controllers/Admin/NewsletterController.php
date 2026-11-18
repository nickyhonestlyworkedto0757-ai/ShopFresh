<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(25);
        $total = NewsletterSubscriber::count();
        return view('admin.newsletter.index', compact('subscribers', 'total'));
    }

    public function exportCsv()
    {
        $subscribers = NewsletterSubscriber::orderBy('created_at')->get();
        return response()->streamDownload(function () use ($subscribers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Subscribed at']);
            foreach ($subscribers as $s) {
                fputcsv($out, [$s->email, $s->created_at->format('Y-m-d H:i:s')]);
            }
            fclose($out);
        }, 'newsletter-subscribers.csv', ['Content-Type' => 'text/csv']);
    }
}