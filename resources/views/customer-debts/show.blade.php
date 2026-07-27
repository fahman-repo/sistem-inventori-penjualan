@extends('adminlte::page')

@section('admin_title', 'Detail Piutang Pelanggan')

@section('content_header', 'Detail Piutang Pelanggan')

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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Informasi Piutang</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 140px;">Pelanggan</th>
                                    <td>{{ $customerDebt->customer->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Invoice</th>
                                    <td>
                                        <a href="{{ route('sales.show', $customerDebt->sale->id) }}">
                                            {{ $customerDebt->sale->invoice_number ?? '-' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Penjualan</th>
                                    <td>{{ $customerDebt->sale->sale_date ? $customerDebt->sale->sale_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Piutang</th>
                                    <td class="font-weight-bold">Rp {{ number_format($customerDebt->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Sudah Dibayar</th>
                                    <td class="text-success font-weight-bold">Rp {{ number_format($customerDebt->paid_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th>Sisa Piutang</th>
                                    <td class="{{ $customerDebt->remaining_amount > 0 ? 'text-danger' : 'text-success' }} font-weight-bold">
                                        Rp {{ number_format($customerDebt->remaining_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jatuh Tempo</th>
                                    <td>{{ $customerDebt->due_date ? $customerDebt->due_date->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $customerDebt->status_badge }}" style="font-size: 14px;">
                                            {{ $customerDebt->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('customer-debts.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Catat Pembayaran Baru</h3>
                        </div>
                        <form action="{{ route('customer-debts.payments.store', $customerDebt->id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                @if($customerDebt->status === 'paid')
                                    <div class="alert alert-success">
                                        <i class="fa fa-check-circle"></i> Piutang ini sudah lunas. Tidak ada pembayaran yang perlu dicatat.
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label for="amount">Jumlah Pembayaran <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="number" name="amount" id="amount"
                                                   class="form-control @error('amount') is-invalid @enderror"
                                                   placeholder="Masukkan jumlah"
                                                   value="{{ old('amount') }}"
                                                   min="1" step="0.01" required>
                                        </div>
                                        @error('amount')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">
                                            Sisa piutang: Rp {{ number_format($customerDebt->remaining_amount, 0, ',', '.') }}
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_date">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" id="payment_date"
                                               class="form-control @error('payment_date') is-invalid @enderror"
                                               value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                        @error('payment_date')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="notes">Catatan</label>
                                        <textarea name="notes" id="notes" rows="2"
                                                  class="form-control @error('notes') is-invalid @enderror"
                                                  placeholder="Catatan pembayaran (opsional)">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                            @if($customerDebt->status !== 'paid')
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save"></i> Simpan Pembayaran
                                </button>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Pembayaran</h3>
                        </div>
                        <div class="card-body">
                            @if($customerDebt->payments->count() > 0)
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>Dicatat Oleh</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customerDebt->payments as $payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                        <td class="font-weight-bold text-success">
                                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        </td>
                                        <td>{{ $payment->user->name ?? '-' }}</td>
                                        <td>{{ $payment->notes ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold">
                                        <td colspan="2" class="text-right">Total Dibayar</td>
                                        <td class="text-success">
                                            Rp {{ number_format($customerDebt->payments->sum('amount'), 0, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            @else
                            <p class="text-muted">Belum ada pembayaran yang dicatat untuk piutang ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
