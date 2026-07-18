@extends('adminlte::page')

@section('admin_title', 'Riwayat Penjualan')

@section('content_header', 'Riwayat Penjualan')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismissal="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(auth()->user()->role !== 'admin')
                    <div class="alert alert-info" role="alert">
                        Menampilkan <strong>riwayat penjualan Anda saja</strong>. Sebagai kasir, Anda hanya dapat melihat transaksi yang Anda buat.
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('sales.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Buat Penjualan Baru
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('sales.index') }}" class="form-inline mb-3">
                            <div class="input-group mr-2">
                                <input type="date" name="date_from" class="form-control"
                                       value="{{ request('date_from') }}" placeholder="Dari">
                            </div>
                            <div class="input-group mr-2">
                                <input type="date" name="date_to" class="form-control"
                                       value="{{ request('date_to') }}" placeholder="Sampai">
                            </div>
                            @if(request('date_from') || request('date_to'))
                                <a href="{{ route('sales.index') }}" class="btn btn-outline-danger mr-2">
                                    <i class="fa fa-times"></i> Reset
                                </a>
                            @endif
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                        </form>

                        @if(request('date_from') || request('date_to'))
                            <div class="alert alert-info" role="alert">
                                <strong>Total Penjualan:</strong> Rp {{ number_format($totalSum ?? 0, 2, ',', '.') }}
                                <span class="float-right">
                                    {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') : '–' }}
                                    s/d
                                    {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') : '–' }}
                                </span>
                            </div>
                        @endif

                        @if($sales->count() > 0)
                        <table id="sales-table" class="table table-bordered table-hover dataTable" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $loop->iteration + ($sales->currentPage() - 1) * $sales->perPage() }}</td>
                                    <td>{{ $sale->invoice_number }}</td>
                                    <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                                    <td>Rp {{ number_format($sale->total, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-success">Selesai</span>
                                    </td>
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
                        <p class="text-muted">Tidak ada penjualan yang ditemukan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#sales-table').DataTable();
    });
</script>
@endsection