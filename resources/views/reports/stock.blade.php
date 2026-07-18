@extends('adminlte::page')

@section('admin_title', 'Laporan Stok')

@section('content_header', 'Laporan Stok Produk')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-chart-bar mr-2"></i>Laporan Stok Produk</h3>
                </div>
                <div class="card-body">
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fa fa-boxes"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Total Produk</span>
                                    <span class="info-box-content">{{ $totalProducts }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fa fa-truck-loading"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Total Stok</span>
                                    <span class="info-box-content">{{ $totalStock }} pcs</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Stok Menipis</span>
                                    <span class="info-box-content">{{ $lowStockProducts }} item</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fa fa-ban"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Stok Habis</span>
                                    <span class="info-box-content">{{ $outOfStock }} item</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('reports.stock') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}" placeholder="Dari">
                        </div>
                        <div class="input-group mr-2">
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}" placeholder="Sampai">
                        </div>
                        <select name="stock_status" class="form-control mr-2">
                            <option value="">Semua Status</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Habis (0)</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Menipis (≤ Min Stock)</option>
                            <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>Aman (&gt; Min Stock)</option>
                        </select>
                        @if(request('date_from') || request('date_to') || request('stock_status'))
                            <a href="{{ route('reports.stock') }}" class="btn btn-outline-danger mr-2">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </form>

                    @if(request('date_from') || request('date_to') || request('stock_status'))
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
                            @if(request('stock_status'))
                                | Status: @if(request('stock_status') == 'out') Habis @elseif(request('stock_status') == 'low') Menipis @else Aman @endif
                            @endif
                        </div>
                    @endif

                    <!-- Products Table -->
                    @if($products->count() > 0)
                        <table id="stock-table" class="table table-bordered table-hover dataTable" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Stock</th>
                                    <th>Min Stock</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr class="{{ $product->stock == 0 ? 'table-danger' : ($product->stock <= $product->min_stock ? 'table-warning' : '') }}">
                                        <td>{{ $product->sku }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>{{ $product->min_stock }}</td>
                                        <td>{{ $product->unit }}</td>
                                        <td>
                                            @if($product->stock == 0)
                                                <span class="badge badge-danger">HABIS</span>
                                            @elseif($product->stock <= $product->min_stock)
                                                <span class="badge badge-warning">MENIPIS</span>
                                            @else
                                                <span class="badge badge-success">AMA</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                        <p class="text-muted">Tidak ada produk yang ditemukan.</p>
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
        $('#stock-table').DataTable();
    });
</script>
@endsection