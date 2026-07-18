@extends('adminlte::page')

@section('admin_title', 'Edit Pembelian')

@section('content_header', 'Edit Pembelian - ' . $purchase->invoice_number)

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Pembelian</h3>
                    </div>
                    <div class="card-body">
                        <form id="purchase-form" action="{{ route('purchases.update', $purchase) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="invoice_number">Invoice Number</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $purchase->invoice_number }}" readonly>
                            </div>

                            <div class="form-group">
                                <label for="supplier_id">Supplier</label>
                                <select class="form-control" id="supplier_id" name="supplier_id">
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="purchase_date">Tanggal Pembelian</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}" required>
                            </div>

                            <input type="hidden" id="purchase-id" value="{{ $purchase->id }}">
                            <input type="hidden" id="products-data" value="{{ $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'buy_price' => $p->buy_price, 'unit' => $p->unit, 'stock' => $p->stock])->toJson() }}">

                            <hr>

                            <h5>Detail Item</h5>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga Beli</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th class="text-center" style="width: 60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="purchase-items">
                                    @foreach($purchase->items as $index => $item)
                                    <tr class="item-row">
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <td style="width: 40%;">
                                            <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                                <option value="">Pilih Produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (Stock: {{ $product->stock }} {{ $product->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="items[{{ $index }}][buy_price]" class="form-control buy-price" step="0.01" min="0" value="{{ $item->buy_price }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" min="1" step="1" required value="{{ $item->quantity }}">
                                        </td>
                                        <td class="text-center">
                                            <input type="number" class="form-control subtotal" readonly value="{{ $item->subtotal }}">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <button type="button" id="add-item" class="btn btn-outline-primary mb-3">
                                <i class="fa fa-plus"></i> Tambah Item
                            </button>

                            <hr>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $purchase->notes) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Total</label>
                                <p class="font-weight-bold">Rp <span id="total-amount">{{ number_format($purchase->total, 2, ',', '.') }}</span></p>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
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

@section('js')
<script>
$(document).ready(function() {
    const products = JSON.parse($('#products-data').val());
    const purchaseId = $('#purchase-id').val();
    let itemCount = {{ $purchase->items->count() }};

    function populateProductSelect(select, selectedProductId = null) {
        let options = '<option value="">Pilih Produk</option>';
        products.forEach(function(p) {
            const selected = selectedProductId && p.id == selectedProductId ? 'selected' : '';
            options += '<option value="' + p.id + '" data-price="' + p.buy_price + '" ' + selected + '>' +
                       p.name + ' (Stock: ' + p.stock + ' ' + p.unit + ')' +
                       '</option>';
        });
        $(select).html(options);
    }

    function calculateSubtotal(row) {
        const price = parseFloat($(row).find('.buy-price').val()) || 0;
        const qty = parseInt($(row).find('.quantity').val()) || 0;
        const subtotal = price * qty;
        $(row).find('.subtotal').val(subtotal);
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        $('.subtotal').each(function() {
            const val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $('#total-amount').text('Rp ' + total.toLocaleString('id-ID'));
    }

    // Initialize product selects for existing items
    $('.product-select').each(function() {
        const selectedProductId = $(this).find('option:selected').val();
        populateProductSelect(this, selectedProductId);
    });

    // Handle product selection
    $(document).on('change', '.product-select', function() {
        const row = $(this).closest('.item-row');
        const selected = $(this).find('option:selected');
        const price = selected.data('price') || 0;
        row.find('.buy-price').val(price);
        calculateSubtotal(row);
    });

    // Handle quantity change
    $(document).on('input', '.quantity', function() {
        const row = $(this).closest('.item-row');
        calculateSubtotal(row);
    });

    // Add new item
    $('#add-item').click(function() {
        const index = itemCount++;
        const newRow = `
            <tr class="item-row">
                <td style="width: 40%;">
                    <select name="items[${index}][product_id]" class="form-control product-select" required>
                        <option value="">Pilih Produk</option>
                    </select>
                </td>
                <td class="text-center">
                    <input type="number" name="items[${index}][buy_price]" class="form-control buy-price" step="0.01" min="0" readonly>
                </td>
                <td class="text-center">
                    <input type="number" name="items[${index}][quantity]" class="form-control quantity" min="1" step="1" required>
                </td>
                <td class="text-center">
                    <input type="number" class="form-control subtotal" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#purchase-items').append(newRow);
        populateProductSelect($('.product-select').last());
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        calculateTotal();
    });

    // Initialize subtotals for existing items
    $('.item-row').each(function() {
        calculateSubtotal($(this));
    });
});
</script>
@endsection