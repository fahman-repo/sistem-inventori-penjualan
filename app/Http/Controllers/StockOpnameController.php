<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOpname;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of stock opnames.
     */
    public function index(): View
    {
        $stockOpnames = StockOpname::with('user', 'items')
            ->latest()
            ->paginate(15);

        return view('stock-opnames.index', compact('stockOpnames'));
    }

    /**
     * Display the specified stock opname.
     */
    public function show(StockOpname $stockOpname): View
    {
        $stockOpname->load('items.product', 'user');

        return view('stock-opnames.show', compact('stockOpname'));
    }

    /**
     * Show the form for creating a new stock opname.
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get();

        return view('stock-opnames.create', compact('products'));
    }

    /**
     * Store a newly created stock opname in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_stock' => 'required|integer|min:0',
        ]);

        $opnameNumber = $this->generateOpnameNumber();

        DB::transaction(function () use ($validated, $opnameNumber) {
            $stockOpname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'user_id' => auth()->id(),
                'opname_date' => $validated['opname_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $systemStock = $product->stock;
                $physicalStock = (int) $item['physical_stock'];
                $difference = $physicalStock - $systemStock;

                $stockOpname->items()->create([
                    'product_id' => $product->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $difference,
                ]);

                // Adjust product stock langsung ke physical_stock
                $product->update(['stock' => $physicalStock]);
            }

            ActivityLogger::log('stock_opname', $stockOpname, null, $stockOpname->toArray());

            return $stockOpname;
        });

        return redirect()->route('stock-opnames.index')
            ->with('success', 'Stock opname berhasil disimpan.');
    }

    /**
     * Generate a unique opname number.
     */
    protected function generateOpnameNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOpname = StockOpname::where('opname_number', 'like', "SO-{$date}-%")
            ->orderBy('opname_number', 'desc')
            ->first();

        if ($lastOpname) {
            $lastNumber = (int) substr($lastOpname->opname_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "SO-{$date}-{$nextNumber}";
    }
}