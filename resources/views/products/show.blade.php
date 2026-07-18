@extends('adminlte::page')

@section('admin_title', 'Detail Produk')

@section('content_header', 'Detail Produk: ' . $product->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Produk</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>SKU</th>
                                <td>{{ $product->sku }}</td>
                            </tr>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>{{ $product->category->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Satuan</th>
                                <td>{{ $product->unit }}</td>
                            </tr>
                            <tr>
                                <th>Harga Beli</th>
                                <td>Rp {{ number_format($product->buy_price, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Harga Jual</th>
                                <td>Rp {{ number_format($product->sell_price, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>Stock</th>
                                <td>
                                    {{ $product->stock }} {{ $product->unit }}
                                    @if($product->isLowStock())
                                        <span class="badge badge-warning">STOCK MENIPIS</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Stock Minimum</th>
                                <td>{{ $product->min_stock }} {{ $product->unit }}</td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection