@extends('adminlte::page')

@section('admin_title', 'Daftar Utang Supplier')

@section('content_header', 'Daftar Utang Supplier')

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

            {{-- Filter Card --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('supplier-debts.index') }}" class="form-inline">
                        <div class="form-group mr-2 mb-2">
                            <label class="mr-2">Status</label>
                            <select name="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-2">
                            <label class="mr-2">Supplier</label>
                            <select name="supplier_id" class="form-control">
                                <option value="">Semua Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('supplier-debts.index') }}" class="btn btn-secondary mb-2 ml-1">
                            <i class="fa fa-refresh"></i> Reset
                        </a>
                        <a href="{{ route('supplier-debts.export', request()->query()) }}" class="btn btn-success mb-2 ml-1">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </a>
                    </form>
                </div>
            </div>

            {{-- Debts Table --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Utang</h3>
                </div>
                <div class="card-body">
                    @if($debts->count() > 0)
                    <table class="table table-bordered table-hover" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Supplier</th>
                                <th>Invoice Pembelian</th>
                                <th>Total Utang</th>
                                <th>Dibayar</th>
                                <th>Sisa Utang</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debts as $debt)
                            <tr>
                                <td>{{ $loop->iteration + ($debts->currentPage() - 1) * $debts->perPage() }}</td>
                                <td>{{ $debt->supplier->name ?? '-' }}</td>
                                <td>{{ $debt->purchase->invoice_number ?? '-' }}</td>
                                <td>Rp {{ number_format($debt->total_amount, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($debt->paid_amount, 0, ',', '.') }}</td>
                                <td class="font-weight-bold {{ $debt->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                                </td>
                                <td>{{ $debt->due_date ? $debt->due_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $debt->status_badge }}">
                                        {{ $debt->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('supplier-debts.show', $debt->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $debts->appends(request()->query())->links() }}
                    </div>
                    @else
                    <p class="text-muted">Tidak ada data utang yang ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Allow Enter key to submit filter form
    $('.form-control').on('keypress', function(e) {
        if (e.which === 13) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endsection