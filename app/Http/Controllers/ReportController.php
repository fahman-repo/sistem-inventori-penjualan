<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Show stock report.
     */
    public function stock(Request $request): View
    {
        $query = Product::with('category')
            ->orderBy('stock');

        // Filter by date range if needed (products added/updated in range)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'low') {
                $query->where('stock', '<=', 'min_stock');
            } elseif ($status === 'out') {
                $query->where('stock', 0);
            } elseif ($status === 'ok') {
                $query->where('stock', '>', 'min_stock')->where('stock', '>', 0);
            }
        }

        $products = $query->paginate(20)->withQueryString();

        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $lowStockProducts = Product::where('stock', '<=', 'min_stock')->count();
        $outOfStock = Product::where('stock', 0)->count();

        return view('reports.stock', compact(
            'products',
            'totalProducts',
            'totalStock',
            'lowStockProducts',
            'outOfStock'
        ));
    }

    /**
     * Show sales report with date filter.
     */
    public function sales(Request $request): View
    {
        $query = Sale::with(['items.product', 'user']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->input('date_to'));
        }

        // Filter by kasir (user)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $sales = $query->latest()->paginate(15)->withQueryString();

        // Calculate totals
        $totalSales = $query->clone()->sum('total');
        $totalItems = $query->clone()->withCount('items')->get()->sum('items_count');

        // For chart data (last 7 days)
        $chartData = $this->getSalesChartData(7);

        // Get users for filter dropdown (only admin)
        $users = null;
        if (auth()->user()->role === 'admin') {
            $users = \App\Models\User::where('role', 'kasir')->orderBy('name')->get();
        }

        return view('reports.sales', compact(
            'sales',
            'totalSales',
            'totalItems',
            'chartData',
            'users'
        ));
    }

    /**
     * Show gross profit report.
     */
    public function profit(Request $request): View
    {
        $query = SaleItem::with(['sale', 'product']);

        // Filter by date range through sale
        if ($request->filled('date_from')) {
            $query->whereHas('sale', function ($q) use ($request) {
                $q->where('sale_date', '>=', $request->input('date_from'));
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('sale', function ($q) use ($request) {
                $q->where('sale_date', '<=', $request->input('date_to'));
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->input('category_id'));
            });
        }

        // Group by product for profit summary
        $profitByProduct = SaleItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(sell_price * quantity) as total_sell'),
            DB::raw('SUM(buy_price * quantity) as total_buy'),
            DB::raw('SUM((sell_price - buy_price) * quantity) as total_profit')
        )
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_profit')
            ->get();

        // Calculate totals
        $totalSell = $profitByProduct->sum('total_sell');
        $totalBuy = $profitByProduct->sum('total_buy');
        $totalProfit = $profitByProduct->sum('total_profit');

        // For chart data (last 7 days)
        $chartData = $this->getProfitChartData(7);

        // Get categories for filter
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('reports.profit', compact(
            'profitByProduct',
            'totalSell',
            'totalBuy',
            'totalProfit',
            'chartData',
            'categories'
        ));
    }

    /**
     * Export sales report to Excel.
     */
    public function exportSalesExcel(Request $request): BinaryFileResponse
    {
        $fileName = 'laporan-penjualan-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new SalesReportExport($request), $fileName);
    }

    /**
     * Get sales chart data for the last N days.
     */
    protected function getSalesChartData(int $days): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        $data = collect();
        $labels = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $labels[] = $date->format('d/m');

            $total = Sale::whereDate('sale_date', $date->format('Y-m-d'))->sum('total');
            $data->push($total);
        }

        return [
            'labels' => $labels,
            'data' => $data->map(fn($v) => (float) $v)->toArray(),
        ];
    }

    /**
     * Get profit chart data for the last N days.
     */
    protected function getProfitChartData(int $days): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        $data = collect();
        $labels = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $labels[] = $date->format('d/m');

            $profit = SaleItem::select(
                DB::raw('SUM((sell_price - buy_price) * quantity) as profit')
            )
                ->whereHas('sale', function ($q) use ($date) {
                    $q->whereDate('sale_date', $date->format('Y-m-d'));
                })
                ->first()
                ->profit;

            $data->push((float) ($profit ?? 0));
        }

        return [
            'labels' => $labels,
            'data' => $data->toArray(),
        ];
    }
}