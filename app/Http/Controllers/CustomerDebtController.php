<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtPaymentRequest;
use App\Models\Customer;
use App\Models\CustomerDebt;
use App\Models\CustomerDebtPayment;
use App\Exports\CustomerDebtExport;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerDebtController extends Controller
{
    public function index(): View
    {
        $debts = CustomerDebt::with(['customer', 'sale'])
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('customer_id'), function ($query) {
                $query->where('customer_id', request('customer_id'));
            })
            ->latest()->paginate(15);

        $customers = Customer::orderBy('name')->get();

        return view('customer-debts.index', compact('debts', 'customers'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $fileName = 'daftar-piutang-pelanggan-' . now()->format('Y-m-d-His') . '.xlsx';

        return Excel::download(new CustomerDebtExport($request), $fileName);
    }

    public function show(CustomerDebt $customerDebt): View
    {
        $customerDebt->load(['customer', 'sale', 'payments.user']);

        return view('customer-debts.show', compact('customerDebt'));
    }

    public function storePayment(StoreDebtPaymentRequest $request, CustomerDebt $customerDebt): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $customerDebt) {
            $payment = CustomerDebtPayment::create([
                'customer_debt_id' => $customerDebt->id,
                'user_id' => auth()->id(),
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $totalPaid = $customerDebt->payments()->sum('amount');

            $status = 'unpaid';
            if ($totalPaid >= $customerDebt->total_amount) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partial';
            }

            $customerDebt->update([
                'paid_amount' => $totalPaid,
                'status' => $status,
            ]);

            ActivityLogger::log('create', $payment, null, $payment->toArray());
        });

        return redirect()->route('customer-debts.show', $customerDebt->id)
            ->with('success', 'Pembayaran piutang berhasil dicatat.');
    }
}
