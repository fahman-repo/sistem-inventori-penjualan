@extends('adminlte::page')

@section('title', 'Detail Stock Opname')

@section('content_header')
    <h1>Detail Stock Opname</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $stockOpname->opname_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <strong>Nomor Opname</strong>
                            <p class="font-weight-bold">{{ $stockOpname->opname_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Tanggal</strong>
                            <p>{{ $stockOpname->opname_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>User</strong>
                            <p>{{ $stockOpname->user->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Catatan</strong>
                            <p>{{ $stockOpname->notes ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="40">No</th>
                                    <th>Nama Produk</th>
                                    <th width="120">SKU</th>
                                    <th width="100" class="text-center">Stok Sistem</th>
                                    <th width="100" class="text-center">Stok Fisik</th>
                                    <th width="120" class="text-center">Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockOpname->items as $index => $item)
                                    @php
                                        $diff = $item->difference;
                                        $diffClass = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted');
                                        $diffLabel = $diff > 0 ? "+{$diff}" : $diff;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? 'Produk dihapus' }}</td>
                                        <td>{{ $item->product->sku ?? '-' }}</td>
                                        <td class="text-center">{{ $item->system_stock }}</td>
                                        <td class="text-center">{{ $item->physical_stock }}</td>
                                        <td class="text-center font-weight-bold {{ $diffClass }}">
                                            {{ $diffLabel }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <em>Tidak ada item.</em>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop