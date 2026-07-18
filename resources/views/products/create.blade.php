@extends('adminlte::page')

@section('admin_title', 'Tambah Produk')

@section('content_header', 'Tambah Produk Baru')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Produk</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('products.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="category_id">Kategori</label>
                                <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories ?? \App\Models\Category::all() as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="name">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="sku">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku"
                                       value="{{ old('sku') }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="unit">Satuan</label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit"
                                       value="{{ old('unit', 'pcs') }}">
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="buy_price">Harga Beli <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('buy_price') is-invalid @enderror" id="buy_price"
                                       name="buy_price" value="{{ old('buy_price', 0) }}" required step="0.01" min="0">
                                @error('buy_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="sell_price">Harga Jual <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('sell_price') is-invalid @enderror" id="sell_price"
                                       name="sell_price" value="{{ old('sell_price', 0) }}" required step="0.01" min="0">
                                @error('sell_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="stock">Stock <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('stock') is-invalid @enderror" id="stock"
                                       name="stock" value="{{ old('stock', 0) }}" required min="0">
                                @error('stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="min_stock">Stock Minimum</label>
                                <input type="number" class="form-control @error('min_stock') is-invalid @enderror" id="min_stock"
                                       name="min_stock" value="{{ old('min_stock', 10) }}" min="1">
                                @error('min_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Batal
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection