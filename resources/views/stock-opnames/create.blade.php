@extends('adminlte::page')

@section('title', 'Stock Opname Baru')

@section('content_header')
    <h1>Stock Opname Baru</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Stock Opname</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('stock-opnames.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="opname_date">Tanggal Opname</label>
                                    <input type="date" name="opname_date" id="opname_date"
                                           class="form-control @error('opname_date') is-invalid @enderror"
                                           value="{{ old('opname_date', date('Y-m-d')) }}" required>
                                    @error('opname_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>Daftar Produk</h5>
                        <p class="text-muted">Isi stok fisik hasil hitungan manual untuk setiap produk.</p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Nama Produk</th>
                                        <th width="120">SKU</th>
                                        <th width="80" class="text-center">Stok Sistem</th>
                                        <th width="140" class="text-center">Stok Fisik</th>
                                        <th width="120" class="text-center">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $index => $product)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $product->name }}
                                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                            </td>
                                            <td>{{ $product->sku }}</td>
                                            <td class="text-center">
                                                <span class="system-stock" data-stock="{{ $product->stock }}">
                                                    {{ $product->stock }}
                                                </span>
                                                <input type="hidden" class="system-stock-input" value="{{ $product->stock }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="items[{{ $index }}][physical_stock]"
                                                       class="form-control form-control-sm physical-stock"
                                                       value="{{ old('items.' . $index . '.physical_stock', $product->stock) }}"
                                                       min="0" style="width: 100px; margin: 0 auto;" required>
                                            </td>
                                            <td class="text-center">
                                                <span class="difference font-weight-bold">0</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <em>Tidak ada produk.</em>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @error('items')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror

                        <hr>

                        <div class="form-group">
                            <label for="notes">Catatan</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                                      rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Stock Opname
                        </button>
                        <a href="{{ route('stock-opnames.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Hitung selisih otomatis di client-side sebagai preview
    function calculateDifference(row) {
        var systemStock = parseInt($(row).find('.system-stock').data('stock')) || 0;
        var physicalStock = parseInt($(row).find('.physical-stock').val()) || 0;
        var diff = physicalStock - systemStock;
        var diffSpan = $(row).find('.difference');

        diffSpan.text(diff);

        // Warna indikator
        if (diff > 0) {
            diffSpan.removeClass('text-danger text-muted').addClass('text-success');
        } else if (diff < 0) {
            diffSpan.removeClass('text-success text-muted').addClass('text-danger');
        } else {
            diffSpan.removeClass('text-success text-danger').addClass('text-muted');
        }
    }

    // Hitung saat input berubah
    $(document).on('input', '.physical-stock', function() {
        calculateDifference($(this).closest('tr'));
    });

    // Hitung awal untuk semua baris
    $('.physical-stock').each(function() {
        calculateDifference($(this).closest('tr'));
    });
});
</script>
@stop