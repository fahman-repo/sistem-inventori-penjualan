<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->when(request('search'), function ($query) {
                $query->where('invoice_number', 'LIKE', '%' . request('search') . '%');
            })
            ->when(request('date_from'), function ($query) {
                $query->where('purchase_date', '>=', request('date_from'));
            })
            ->when(request('date_to'), function ($query) {
                $query->where('purchase_date', '<=', request('date_to'));
            })
            ->latest()->paginate(15);

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $products = Product::where('stock', '>', 0)->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.create', compact('products', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Calculate total
        $total = 0;
        foreach ($validated['items'] as $item) {
            $total += $item['quantity'] * $item['buy_price'];
        }

        // Generate unique invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Use DB::transaction for data integrity
        $purchase = DB::transaction(function () use ($validated, $total, $invoiceNumber) {
            $purchase = Purchase::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => auth()->id(),
                'supplier_id' => $validated['supplier_id'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'payment_status' => $validated['payment_status'] ?? 'cash',
            ]);

            // Create purchase items and update stock
            foreach ($validated['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'buy_price' => $item['buy_price'],
                    'subtotal' => $item['quantity'] * $item['buy_price'],
                ]);

                // Update product stock - automatically increase
                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            // If payment is credit, auto-create supplier_debt record
            if (($validated['payment_status'] ?? 'cash') === 'credit' && !empty($validated['supplier_id'])) {
                SupplierDebt::create([
                    'purchase_id'  => $purchase->id,
                    'supplier_id'  => $validated['supplier_id'],
                    'total_amount' => $total,
                    'paid_amount'  => 0,
                    'due_date'     => $validated['due_date'] ?? null,
                    'status'       => 'unpaid',
                ]);
            }

            return $purchase;
        });

        ActivityLogger::log('create', $purchase, null, $purchase->toArray());

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase): View
    {
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase): View
    {
        $products = Product::where('stock', '>', 0)->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'products', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $validated = $request->validated();

        // Calculate total
        $total = 0;
        foreach ($validated['items'] as $item) {
            $total += $item['quantity'] * $item['buy_price'];
        }

        DB::transaction(function () use ($validated, $total, $purchase) {
            // Delete old items
            $purchase->items()->delete();

            // Update purchase header - keep existing invoice_number if not provided
            $purchase->update([
                'invoice_number' => $validated['invoice_number'] ?? $purchase->invoice_number,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'purchase_date' => $validated['purchase_date'],
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'payment_status' => $validated['payment_status'] ?? $purchase->payment_status,
            ]);

            // Create new purchase items
            foreach ($validated['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'buy_price' => $item['buy_price'],
                    'subtotal' => $item['quantity'] * $item['buy_price'],
                ]);
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase): RedirectResponse
    {
        DB::transaction(function () use ($purchase) {
            // Decrease stock for each item
            foreach ($purchase->items as $item) {
                Product::where('id', $item->product_id)->decrement('stock', $item->quantity);
            }

            // Delete purchase items
            $purchase->items()->delete();

            // Delete purchase
            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian berhasil dihapus.');
    }

    /**
     * Generate a unique invoice number for purchase.
     */
    protected function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPurchase = Purchase::where('invoice_number', 'like', "PO-{$date}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->invoice_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "PO-{$date}-{$nextNumber}";
    }
}