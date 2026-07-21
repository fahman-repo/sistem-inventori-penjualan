@extends('adminlte::page')

@section('admin_title', 'Detail Supplier')

@section('content_header', 'Detail Supplier')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Supplier</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 120px;">Nama</th>
                                    <td>{{ $supplier->name }}</td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td>{{ $supplier->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $supplier->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $supplier->address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Dibuat</th>
                                    <td>{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Pembelian (10 Terakhir)</h3>
                        </div>
                        <div class="card-body">
                            @if($supplier->purchases->count() > 0)
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status Bayar</th>
                                        <th>Status Utang</th>
                                        <th class="text-center">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->invoice_number }}</td>
                                        <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                        <td>Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                                        <td>
                                            @if($purchase->payment_status === 'credit')
                                                <span class="badge badge-warning">Credit (Utang)</span>
                                            @else
                                                <span class="badge badge-success">Cash (Lunas)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($purchase->supplierDebt)
                                                <span class="badge badge-{{ $purchase->supplierDebt->status_badge }}">
                                                    {{ $purchase->supplierDebt->status_label }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    Sisa: Rp {{ number_format($purchase->supplierDebt->remaining_amount, 0, ',', '.') }}
                                                </small>
                                            @elseif($purchase->payment_status === 'credit')
                                                <span class="badge badge-secondary">Tidak Ada</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @if($purchase->supplierDebt)
                                                <a href="{{ route('supplier-debts.show', $purchase->supplierDebt->id) }}" class="btn btn-sm btn-warning" title="Lihat Utang">
                                                    <i class="fa fa-hand-holding-usd"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted">Belum ada transaksi pembelian dari supplier ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection