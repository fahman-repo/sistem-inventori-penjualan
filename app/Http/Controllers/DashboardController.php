<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SupplierDebt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Summary data
        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $lowStockProducts = Product::where('stock', '<=', 'min_stock')->count();
        $outOfStock = Product::where('stock', 0)->count();

        // Today's sales
        $todaySales = Sale::whereDate('sale_date', today())->sum('total');
        $todaySalesCount = Sale::whereDate('sale_date', today())->count();

        // Chart data for last 7 days
        $chartData = $this->getSalesChartData(7);

        // Profit data for last 7 days
        $profitData = $this->getProfitChartData(7);

        // Recent sales (last 5)
        $recentSales = Sale::with('items')
            ->when($user->role !== 'admin', fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->limit(5)
            ->get();

        // Upcoming/overdue supplier debts (unpaid/partial, due past or within 7 days)
        $dueDebts = SupplierDebt::with('supplier')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($query) {
                $query->where('due_date', '<=', now()->addDays(7))
                    ->orWhereNull('due_date');
            })
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
            ->limit(10)
            ->get();

        $totalDueDebts = $dueDebts->count();
        $totalDueAmount = $dueDebts->sum('remaining_amount');

        return view('dashboard', compact(
            'totalProducts',
            'totalStock',
            'lowStockProducts',
            'outOfStock',
            'todaySales',
            'todaySalesCount',
            'chartData',
            'profitData',
            'recentSales',
            'dueDebts',
            'totalDueDebts',
            'totalDueAmount'
        ));
    }

    /**
     * Get sales chart data for the last N days.
     */
    protected function getSalesChartData(int $days): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        $labels = [];
        $salesData = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $labels[] = $date->format('d/m');

            $total = Sale::whereDate('sale_date', $date->format('Y-m-d'))->sum('total');
            $salesData[] = (float) $total;
        }

        return [
            'labels' => $labels,
            'data' => $salesData,
        ];
    }

    /**
     * Get profit chart data for the last N days.
     */
    protected function getProfitChartData(int $days): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        $labels = [];
        $profitData = [];

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

            $profitData[] = (float) ($profit ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $profitData,
        ];
    }
}