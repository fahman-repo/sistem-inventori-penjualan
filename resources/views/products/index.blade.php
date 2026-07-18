@extends('adminlte::page')

@section('admin_title', 'Daftar Produk')

@section('content_header', 'Daftar Produk')

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
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Produk
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="{{ route('products.index') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari produk..."
                                   value="{{ request('search') }}" style="width: 250px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <select name="category_id" class="form-control mr-2" style="width: 200px;">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @if(request('search') || request('category_id'))
                            <a href="{{ route('products.index') }}" class="btn btn-outline-danger">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                    </form>

                    @if($products->count() > 0)
                    <table id="products-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>SKU</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Unit</th>
                                <th>HBeli</th>
                                <th>HJual</th>
                                <th>Stock</th>
                                <th>Min Stock</th>
                                <th>Status</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr class="{{ $product->isLowStock() ? 'table-warning' : '' }}">
                                <td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>{{ $product->unit }}</td>
                                <td>Rp {{ number_format($product->buy_price, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($product->sell_price, 2, ',', '.') }}</td>
                                <td>{{ $product->stock }} {{ $product->unit }}</td>
                                <td>{{ $product->min_stock }} {{ $product->unit }}</td>
                                <td>
                                    @if($product->isLowStock())
                                        <span class="badge badge-warning">STOCK MENIPIS</span>
                                    @else
                                        <span class="badge badge-success">STOK CUKUP</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
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
        $('#products-table').DataTable();
    });
</script>
@endsection