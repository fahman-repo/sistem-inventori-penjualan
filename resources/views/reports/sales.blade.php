@extends('adminlte::page')

@section('admin_title', 'Laporan Penjualan')

@section('content_header', 'Laporan Penjualan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-chart-line mr-2"></i>Laporan Penjualan</h3>
                </div>
                <div class="card-body">
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fa fa-cash-register"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Total Penjualan</span>
                                    <span class="info-box-content">Rp {{ number_format($totalSales, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fa fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Total Item Terjual</span>
                                    <span class="info-box-content">{{ $totalItems }} item</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fa fa-chart-bar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Transaksi</span>
                                    <span class="info-box-content">{{ $sales->total() }} transaksi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('reports.sales') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}" placeholder="Dari">
                        </div>
                        <div class="input-group mr-2">
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}" placeholder="Sampai">
                        </div>
                        @if(auth()->user()->role === 'admin' && request('user_id'))
                            <div class="input-group mr-2">
                                <select name="user_id" class="form-control">
                                    <option value="">Semua Kasir</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        @if(request('date_from') || request('date_to') || request('user_id'))
                            <a href="{{ route('reports.sales') }}" class="btn btn-outline-danger mr-2">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </form>

                    @if(request('date_from') || request('date_to'))
                        <div class="alert alert-info" role="alert">
                            <strong>Filter:</strong>
                            @if(request('date_from'))
                                {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }}
                            @else
                                –
                            @endif
                            s/d
                            @if(request('date_to'))
                                {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}
                            @else
                                –
                            @endif
                            @if(request('user_id'))
                                | Kasir: {{ $users->find(request('user_id'))->name ?? 'Semua' }}
                            @endif
                        </div>
                    @endif()

                    <!-- Sales Table -->
                    @if($sales->count() > 0)
                        <table id="sales-report-table" class="table table-bordered table-hover dataTable" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Kasir</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                    <tr>
                                        <td>{{ $loop->iteration + ($sales->currentPage() - 1) * $sales->perPage() }}</td>
                                        <td>{{ $sale->invoice_number }}</td>
                                        <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                                        <td>{{ $sale->user->name }}</td>
                                        <td>{{ $sale->items->count() }} item</td>
                                        <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-info">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Tidak ada data penjualan yang ditemukan.</p>
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
        $('#sales-report-table').DataTable();
    });
</script>
@endsection