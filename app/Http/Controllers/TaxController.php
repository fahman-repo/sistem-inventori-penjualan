<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Tax;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(): View
    {
        $taxes = Tax::latest()->paginate(15);

        return view('taxes.index', compact('taxes'));
    }

    public function create(): View
    {
        return view('taxes.create');
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        $tax = Tax::create($request->validated());

        ActivityLogger::log('create', $tax, null, $tax->toArray());

        return redirect()->route('taxes.index')
            ->with('success', 'Pajak berhasil ditambahkan.');
    }

    public function show(Tax $tax): View
    {
        return view('taxes.show', compact('tax'));
    }

    public function edit(Tax $tax): View
    {
        return view('taxes.edit', compact('tax'));
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $oldValues = $tax->toArray();
        $tax->update($request->validated());

        ActivityLogger::log('update', $tax, $oldValues, $tax->fresh()->toArray());

        return redirect()->route('taxes.index')
            ->with('success', 'Pajak berhasil diperbarui.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        if ($tax->purchases()->count() > 0 || $tax->sales()->count() > 0) {
            return redirect()->route('taxes.index')
                ->with('error', 'Pajak tidak dapat dihapus karena masih digunakan dalam transaksi.');
        }

        $oldValues = $tax->toArray();
        $tax->delete();

        ActivityLogger::log('delete', $tax, $oldValues, null);

        return redirect()->route('taxes.index')
            ->with('success', 'Pajak berhasil dihapus.');
    }
}
