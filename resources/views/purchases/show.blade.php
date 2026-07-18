@extends('adminlte::page')

@section('admin_title', 'Detail Pembelian')

@section('content_header', 'Detail Pembelian #' . $purchase->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Transaksi Pembelian</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Invoice</th>
                                <td>{{ $purchase->invoice_number }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td>{{ $purchase->supplier->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Input oleh</th>
                                <td>{{ $purchase->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td>Rp {{ number_format($purchase->total, 2, ',', '.') }}</td>
                            </tr>
                            @if($purchase->notes)
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $purchase->notes }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <h5>Detail Item</h5>
                    <table id="purchase-items-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga Beli</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }} {{ $item->product->unit }}</td>
                                <td>Rp {{ number_format($item->buy_price, 2, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#purchase-items-table').DataTable();
    });
</script>
@endsection