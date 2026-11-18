<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        [$from, $to, $rows, $totals] = $this->salesData($request);
        return view('admin.reports.sales', compact('from', 'to', 'rows', 'totals'));
    }

    public function salesCsv(Request $request)
    {
        [$from, $to, $rows, $totals] = $this->salesData($request);
        $filename = 'sales-report-' . $from . '-to-' . $to . '.csv';
        return response()->streamDownload(function () use ($rows, $totals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Orders', 'Revenue (KES)']);
            foreach ($rows as $row) {
                fputcsv($out, [$row->day, $row->orders, number_format($row->total, 2, '.', '')]);
            }
            fputcsv($out, []);
            fputcsv($out, ['TOTAL', $totals['orders'], number_format($totals['revenue'], 2, '.', '')]);
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function salesPdf(Request $request)
    {
        [$from, $to, $rows, $totals] = $this->salesData($request);
        $pdf = Pdf::loadView('admin.reports.sales_pdf', compact('from', 'to', 'rows', 'totals'));
        return $pdf->download('sales-report-' . $from . '-to-' . $to . '.pdf');
    }

    public function profit(Request $request)
    {
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $rows = OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * price) as revenue'))
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->where('status', '!=', 'cancelled'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) {
                $product = $row->product_id ? Product::find($row->product_id) : null;
                $cost = $product ? (float) $product->cost_price : 0;
                $row->cost = $row->qty * $cost;
                $row->profit = $row->revenue - $row->cost;
                return $row;
            });
        return view('admin.reports.profit', compact('rows', 'from', 'to'));
    }

    public function products()
    {
        $products = Product::with('category')->orderBy('stock')->paginate(20);
        $sales = OrderItem::select('product_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * price) as revenue'))
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('product_id')->get()->keyBy('product_id');
        return view('admin.reports.products', compact('products', 'sales'));
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total')
            ->orderByDesc('total_spent')
            ->paginate(20);
        return view('admin.reports.customers', compact('customers'));
    }

    private function salesData(Request $request): array
    {
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $rows = Order::select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as orders'), DB::raw('SUM(total) as total'))
            ->where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('day')->orderBy('day')->get();
        $totals = ['orders' => (int) $rows->sum('orders'), 'revenue' => (float) $rows->sum('total')];
        return [$from, $to, $rows, $totals];
    }
}
