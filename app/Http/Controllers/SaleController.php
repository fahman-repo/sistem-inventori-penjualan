<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\ActivityLogger;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Admin can see all sales, kasir only sees their own
        $query = Sale::with('items')->when(
            auth()->user()->role !== 'admin',
            fn($q) => $q->where('user_id', auth()->id())
        );

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->input('date_to'));
        }

        // Calculate total sum for the filtered results (before pagination)
        $totalSum = $query->clone()->latest()->sum('total');

        $sales = $query->latest()->paginate(15)->withQueryString();

        return view('sales.index', compact('sales', 'totalSum'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $products = Product::where('stock', '>', 0)->get();

        return view('sales.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Calculate total
        $total = 0;
        foreach ($validated['items'] as $item) {
            $total += $item['quantity'] * $item['sell_price'];
        }

        // Generate unique invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Use DB::transaction for data integrity
        $sale = DB::transaction(function () use ($validated, $total, $invoiceNumber) {
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => auth()->id(),
                'sale_date' => $validated['sale_date'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create sale items and decrease stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'buy_price' => $product->buy_price,
                    'sell_price' => $item['sell_price'],
                    'subtotal' => $item['quantity'] * $item['sell_price'],
                ]);

                // Decrease product stock - automatically reduce
                Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
            }

            return $sale;
        });

        ActivityLogger::log('create', $sale, null, $sale->toArray());

        return redirect()->route('sales.index')
            ->with('success', 'Penjualan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale): View
    {
        // Only allow owner or admin to view
        if ($sale->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale): View
    {
        // Only allow owner or admin to edit
        if ($sale->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $products = Product::where('stock', '>', 0)->get();

        return view('sales.edit', compact('sale', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        // Only allow owner or admin to update
        if ($sale->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validated();

        // Calculate new total
        $newTotal = 0;
        foreach ($validated['items'] as $item) {
            $newTotal += $item['quantity'] * $item['sell_price'];
        }

        // Use DB::transaction for data integrity
        DB::transaction(function () use ($validated, $newTotal, $sale) {
            // Get current items
            $currentItems = $sale->items->keyBy('product_id');

            // Process new items
            foreach ($validated['items'] as $item) {
                $productId = $item['product_id'];
                $currentItem = $currentItems->get($productId);

                if ($currentItem) {
                    // Product already exists in this sale
                    $quantityDiff = $item['quantity'] - $currentItem->quantity;

                    if ($quantityDiff > 0) {
                        // Increasing quantity - reduce stock more
                        Product::where('id', $productId)->decrement('stock', $quantityDiff);
                    } elseif ($quantityDiff < 0) {
                        // Decreasing quantity - increase stock
                        Product::where('id', $productId)->increment('stock', abs($quantityDiff));
                    }

                    // Update existing sale item
                    $currentItem->update([
                        'quantity' => $item['quantity'],
                        'sell_price' => $item['sell_price'],
                        'subtotal' => $item['quantity'] * $item['sell_price'],
                    ]);
                } else {
                    // New product for this sale
                    $sale->items()->create([
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'sell_price' => $item['sell_price'],
                        'subtotal' => $item['quantity'] * $item['sell_price'],
                    ]);

                    // Decrease stock for new item
                    Product::where('id', $productId)->decrement('stock', $item['quantity']);
                }
            }

            // Remove items that are no longer in the sale
            foreach ($currentItems as $productId => $item) {
                $found = collect($validated['items'])->contains('product_id', $productId);
                if (!$found) {
                    // Restore stock
                    Product::where('id', $productId)->increment('stock', $item->quantity);
                    // Delete the sale item
                    $item->delete();
                }
            }

            // Update sale total and notes
            $sale->update([
                'total' => $newTotal,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('sales.index')
            ->with('success', 'Penjualan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale): RedirectResponse
    {
        // Only allow owner or admin to delete
        if ($sale->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        // Restore stock when deleting a sale
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
            }

            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Penjualan berhasil dihapus.');
    }

    /**
     * Print the sale invoice as PDF.
     */
    public function print(Sale $sale): \Symfony\Component\HttpFoundation\Response
    {
        // Only allow owner or admin to print
        if ($sale->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $sale->load('items.product', 'user');

        $html = view('sales.invoice', compact('sale'))->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('invoice-' . $sale->invoice_number . '.pdf');
    }

    /**
     * Generate a unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastSale = Sale::where('invoice_number', 'like', "INV-{$date}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->invoice_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "INV-{$date}-{$nextNumber}";
    }
}