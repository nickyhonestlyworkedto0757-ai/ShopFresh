<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\ActivityLogger;
use App\Services\MpesaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private function filteredQuery(Request $request)
    {
        $query = Payment::with('order:id,order_number,customer_name,customer_phone')->latest();
        if ($request->string('method')->toString()) {
            $query->where('method', $request->string('method'));
        }
        if ($request->string('status')->toString()) {
            $query->where('status', $request->string('status'));
        }
        if ($q = trim((string) $request->input('q'))) {
            $query->where(fn ($w) => $w->where('transaction_id', 'like', "%{$q}%")
                ->orWhere('receipt_number', 'like', "%{$q}%")
                ->orWhere('checkout_request_id', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$q}%")->orWhere('customer_name', 'like', "%{$q}%")->orWhere('customer_phone', 'like', "%{$q}%")));
        }
        return $query;
    }

    public function index(Request $request)
    {
        $payments = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $summary = [
            'paid' => Payment::where('status', 'paid')->sum('amount'),
            'pending' => Payment::where('status', 'pending')->sum('amount'),
            'failed' => Payment::where('status', 'failed')->count(),
            'cancelled' => Payment::whereIn('status', ['cancelled', 'timeout'])->count(),
            'refunded' => Payment::where('status', 'refunded')->sum('amount'),
            'mpesa_total' => Payment::where('method', 'mpesa')->sum('amount'),
            'mpesa_paid' => Payment::where('method', 'mpesa')->where('status', 'paid')->count(),
        ];
        return view('admin.payments.index', compact('payments', 'summary'));
    }

    public function show(Payment $payment)
    {
        $payment->load('order.items');
        return view('admin.payments.show', compact('payment'));
    }

    public function exportCsv(Request $request)
    {
        $payments = $this->filteredQuery($request)->get();
        return response()->streamDownload(function () use ($payments) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Order', 'Customer', 'Phone', 'Method', 'Amount', 'Status', 'Receipt', 'CheckoutRequestID']);
            foreach ($payments as $p) {
                fputcsv($out, [
                    $p->created_at->format('Y-m-d H:i:s'),
                    $p->order?->order_number ?? ('#' . $p->order_id),
                    $p->order?->customer_name,
                    $p->phone ?? $p->order?->customer_phone,
                    strtoupper($p->method),
                    number_format($p->amount, 2, '.', ''),
                    $p->status,
                    $p->receipt_number ?? $p->transaction_id ?? '',
                    $p->checkout_request_id ?? '',
                ]);
            }
            fclose($out);
        }, 'payments-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        $payments = $this->filteredQuery($request)->get();
        $summary = [
            'count' => $payments->count(),
            'paid' => $payments->where('status', 'paid')->sum('amount'),
            'pending' => $payments->where('status', 'pending')->sum('amount'),
            'failed' => $payments->whereIn('status', ['failed', 'cancelled', 'timeout'])->count(),
        ];
        $pdf = Pdf::loadView('admin.payments.report_pdf', compact('payments', 'summary'));
        return $pdf->download('payments-' . now()->format('Y-m-d') . '.pdf');
    }

    /** Ask Daraja for the live status of a pending M-Pesa transaction. */
    public function queryMpesa(Payment $payment)
    {
        if ($payment->method !== 'mpesa' || ! $payment->checkout_request_id) {
            return back()->with('error', 'No M-Pesa checkout request ID recorded for this payment.');
        }
        if ($payment->status === 'paid') {
            return back()->with('success', 'This payment is already confirmed as paid.');
        }

        [$ok, $response] = app(MpesaService::class)->statusQuery($payment->checkout_request_id);
        $resultCode = $response['ResultCode'] ?? null;

        if ($ok && (string) $resultCode === '0') {
            $payment->update([
                'status' => 'paid',
                'result_code' => 0,
                'result_desc' => $response['ResultDesc'] ?? 'Query confirmed payment',
                'callback_received_at' => now(),
                'verified_at' => now(),
                'raw_response' => json_encode($response),
            ]);
            $payment->order?->update(['payment_status' => 'paid']);
            ActivityLogger::log('payment.query', $payment->order?->order_number, 'Payments confirmed via STK query');
            return back()->with('success', 'M-Pesa confirmed: payment completed. Order marked as paid.');
        }

        if ($ok && (string) $resultCode === '1032') {
            $payment->update(['status' => 'cancelled', 'result_code' => 1032, 'result_desc' => $response['ResultDesc'] ?? 'Cancelled by user', 'raw_response' => json_encode($response)]);
            $payment->order?->update(['payment_status' => 'failed']);
            return back()->with('success', 'M-Pesa reports: transaction cancelled by the customer.');
        }

        $payment->update(['result_code' => is_numeric($resultCode) ? (int) $resultCode : null, 'result_desc' => $response['ResultDesc'] ?? $response['message'] ?? 'Query failed', 'raw_response' => json_encode($response)]);
        return back()->with('error', 'M-Pesa status: ' . ($response['ResultDesc'] ?? $response['message'] ?? 'Payment still pending or failed.'));
    }

    public function verify(Payment $payment)
    {
        $payment->update(['status' => 'paid', 'verified_at' => now()]);
        $payment->order?->update(['payment_status' => 'paid']);
        ActivityLogger::log('payment.verify', $payment->order?->order_number, 'Payment verified manually');
        return back()->with('success', 'Payment marked as verified.');
    }

    public function markFailed(Payment $payment)
    {
        $payment->update(['status' => 'failed']);
        $payment->order?->update(['payment_status' => 'failed']);
        ActivityLogger::log('payment.failed', $payment->order?->order_number, 'Payment marked as failed');
        return back()->with('success', 'Payment marked as failed.');
    }
}
