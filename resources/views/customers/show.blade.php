@extends('adminlte::page')

@section('admin_title', 'Detail Pelanggan')

@section('content_header', 'Detail Pelanggan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Pelanggan</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 100px;">Nama</th>
                                    <td>{{ $customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td>{{ $customer->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $customer->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td>{{ $customer->address ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Terdaftar</th>
                                    <td>{{ $customer->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Total Transaksi</th>
                                    <td>{{ $customer->sales->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Total Piutang</th>
                                    <td class="font-weight-bold {{ $customer->debts->sum('remaining_amount') > 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($customer->debts->sum('remaining_amount'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Pembelian</h3>
                        </div>
                        <div class="card-body">
                            @if($customer->sales->count() > 0)
                            <table class="table table-bordered table-hover" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Invoice</th>
                                        <th>Total</th>
                                        <th>Status Bayar</th>
                                        <th>Status Piutang</th>
                                        <th class="text-center">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->sales->sortByDesc('sale_date') as $sale)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                        <td>
                                            @if($sale->payment_status === 'cash')
                                                <span class="badge badge-success">Lunas</span>
                                            @else
                                                <span class="badge badge-warning">Kredit</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sale->customerDebt)
                                                <span class="badge badge-{{ $sale->customerDebt->status_badge }}">
                                                    {{ $sale->customerDebt->status_label }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted">Belum ada transaksi pembelian dari pelanggan ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
