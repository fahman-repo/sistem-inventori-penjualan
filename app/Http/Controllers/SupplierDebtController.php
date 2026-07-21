<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtPaymentRequest;
use App\Models\Supplier;
use App\Models\SupplierDebt;
use App\Models\SupplierDebtPayment;
use App\Exports\SupplierDebtExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SupplierDebtController extends Controller
{
    /**
     * Display a listing of supplier debts with filters.
     */
    public function index(): View
    {
        $debts = SupplierDebt::with(['supplier', 'purchase'])
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('supplier_id'), function ($query) {
                $query->where('supplier_id', request('supplier_id'));
            })
            ->latest()->paginate(15);

        $suppliers = Supplier::orderBy('name')->get();

        return view('supplier-debts.index', compact('debts', 'suppliers'));
    }

    /**
     * Display the specified debt with payment history and payment form.
     */
    public function show(SupplierDebt $supplierDebt): View
    {
        $supplierDebt->load(['supplier', 'purchase', 'payments.user']);

        return view('supplier-debts.show', compact('supplierDebt'));
    }

    /**
     * Export supplier debts to Excel, respecting active filters.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $fileName = 'daftar-utang-supplier-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new SupplierDebtExport($request), $fileName);
    }

    /**
     * Record a new payment for a debt.
     */
    public function storePayment(StoreDebtPaymentRequest $request, SupplierDebt $supplierDebt): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $supplierDebt) {
            // Create payment record
            SupplierDebtPayment::create([
                'supplier_debt_id' => $supplierDebt->id,
                'user_id' => auth()->id(),
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Recalculate paid_amount from all payments
            $totalPaid = $supplierDebt->payments()->sum('amount');

            // Determine new status
            $status = 'unpaid';
            if ($totalPaid >= $supplierDebt->total_amount) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            }

            // Update debt record
            $supplierDebt->update([
                'paid_amount' => $totalPaid,
                'status' => $status,
            ]);
        });

        return redirect()->route('supplier-debts.show', $supplierDebt->id)
            ->with('success', 'Pembayaran utang berhasil dicatat.');
    }
}