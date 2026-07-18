@extends('adminlte::page')

@section('admin_title', 'Detail Penjualan')

@section('content_header', 'Detail Penjualan #' . $sale->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Transaksi Penjualan</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Invoice</th>
                                <td>{{ $sale->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Kasir</th>
                                <td>{{ $sale->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td>Rp {{ number_format($sale->total, 2, ',', '.') }}</td>
                            </tr>
                            @if($sale->notes)
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $sale->notes }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <h5>Detail Item</h5>
                    <table id="sale-items-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Kode Barang</th>
                                <th>Harga Jual</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->product->sku }}</td>
                                <td>Rp {{ number_format($item->sell_price, 2, ',', '.') }}</td>
                                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                                <td>Rp {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                   
                   @if(auth()->user()->role === 'admin')
                    <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-primary float-right" onclick="return confirm('Cetak invoice PDF untuk transaksi {{ $sale->invoice_number }}?')">
                        <i class="fa fa-file-pdf"></i> Cetak Invoice
                    </a>
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
        $('#sale-items-table').DataTable();
    });
</script>
@endsection