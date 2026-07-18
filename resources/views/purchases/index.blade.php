@extends('adminlte::page')

@section('admin_title', 'Riwayat Pembelian')

@section('content_header', 'Riwayat Pembelian')

@section('content')
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

            <div class="card">
                <div class="card-header">
                    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Buat Pembelian Baru
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('purchases.index') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari Invoice..."
                                   value="{{ request('search') }}" style="width: 200px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <input type="date" name="date_from" class="form-control mr-2" value="{{ request('date_from') }}">
                        <input type="date" name="date_to" class="form-control mr-2" value="{{ request('date_to') }}">
                        @if(request('search') || request('date_from') || request('date_to'))
                            <a href="{{ route('purchases.index') }}" class="btn btn-outline-danger">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                    </form>

                    @if($purchases->count() > 0)
                    <table id="purchases-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                            <tr>
                                <td>{{ $loop->iteration + ($purchases->currentPage() - 1) * $purchases->perPage() }}</td>
                                <td>{{ $purchase->invoice_number }}</td>
                                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                <td>{{ $purchase->supplier->name ?? '-' }}</td>
                                <td>Rp {{ number_format($purchase->total, 2, ',', '.') }}</td>
                                <td><span class="badge badge-success">Selesai</span></td>
                                <td class="text-center">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted">Tidak ada pembelian yang ditemukan.</p>
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
        $('#purchases-table').DataTable();
    });
</script>
@endsection