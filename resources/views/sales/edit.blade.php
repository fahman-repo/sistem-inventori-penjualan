@extends('adminlte::page')

@section('admin_title', 'Edit Penjualan')

@section('content_header', 'Edit Penjualan - ' . $sale->invoice_number)

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Penjualan</h3>
                    </div>
                    <div class="card-body">
                        <form id="sale-form" action="{{ route('sales.update', $sale) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="invoice_number">Invoice Number</label>
                                <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $sale->invoice_number }}" readonly>
                            </div>

                            <div class="form-group">
                                <label for="sale_date">Tanggal Penjualan</label>
                                <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ old('sale_date', $sale->sale_date->format('Y-m-d')) }}" required>
                            </div>

                            <input type="hidden" id="sale-id" value="{{ $sale->id }}">
                            <input type="hidden" id="products-data" value="{{ $products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'sell_price' => $p->sell_price, 'unit' => $p->unit, 'stock' => $p->stock])->toJson() }}">

                            <hr>

                            <h5>Detail Item</h5>
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga Jual</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th class="text-center" style="width: 60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="sale-items">
                                    @foreach($sale->items as $index => $item)
                                    <tr class="item-row">
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <td style="width: 40%;">
                                            <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                                                <option value="">Pilih Produk</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                            data-price="{{ $product->sell_price }}"
                                                            data-stock="{{ $product->stock }}"
                                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }} (Stock: {{ $product->stock }} {{ $product->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="items[{{ $index }}][sell_price]" class="form-control sell-price" step="0.01" min="0" value="{{ $item->sell_price }}" readonly>
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
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $sale->notes) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Total</label>
                                <p class="font-weight-bold">Rp <span id="total-amount">{{ number_format($sale->total, 2, ',', '.') }}</span></p>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Batal
                            </a>
                        </form>
                    </div>
                    @if(auth()->user()->role !== 'admin')
                    <div class="card-footer">
                        <small class="text-info"><i class="fa fa-user"></i> Mode Kasir - Anda hanya dapat melihat dan mengedit transaksi Anda sendiri</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
@parent
<script>
$(document).ready(function() {
    const products = JSON.parse($('#products-data').val());
    const saleId = $('#sale-id').val();
    let itemCount = {{ $sale->items->count() }};

    function populateProductSelect(select, selectedProductId = null) {
        let options = '<option value="">Pilih Produk</option>';
        products.forEach(function(p) {
            const selected = selectedProductId && p.id == selectedProductId ? 'selected' : '';
            options += '<option value="' + p.id + '" data-price="' + p.sell_price + '" data-stock="' + p.stock + '" ' + selected + '>' +
                       p.name + ' (Stock: ' + p.stock + ' ' + p.unit + ')' +
                       '</option>';
        });
        $(select).html(options);
    }

    function validateQuantity(row) {
        const select = row.find('.product-select');
        const quantityInput = row.find('.quantity');
        const selected = select.find('option:selected');
        const availableStock = parseInt(selected.data('stock')) || 0;
        const enteredQuantity = parseInt(quantityInput.val()) || 0;

        // Remove previous validation classes
        quantityInput.removeClass('is-invalid');
        row.find('.stock-error').remove();

        // Validate quantity against stock
        if (enteredQuantity > availableStock && availableStock > 0) {
            quantityInput.addClass('is-invalid');
            row.find('.quantity-label').after(
                '<span class="stock-error text-danger form-text">Stok hanya ' + availableStock + ' tersedia!</span>'
            );
            return false;
        }
        return true;
    }

    function calculateSubtotal(row) {
        const price = parseFloat($(row).find('.sell-price').val()) || 0;
        const qty = parseInt($(row).find('.quantity').val()) || 0;
        const subtotal = price * qty;
        $(row).find('.subtotal').val(subtotal);
        calculateTotal();
        validateQuantity(row);
    }

    function calculateTotal() {
        let total = 0;
        $('.subtotal').each(function() {
            const val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $('#total-amount').text('Rp ' + total.toLocaleString('id-ID'));
    }

    function validateAllItems() {
        let isValid = true;
        $('.item-row').each(function() {
            if (!validateQuantity(this)) {
                isValid = false;
            }
        });
        return isValid;
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
        const availableStock = parseInt(selected.data('stock')) || 0;
        const currentQty = parseInt(row.find('.quantity').val()) || 0;

        row.find('.sell-price').val(price);

        // Set max attribute for quantity input
        row.find('.quantity').attr('max', availableStock);

        // Validate current quantity
        if (currentQty > availableStock && availableStock > 0) {
            row.find('.quantity').addClass('is-invalid');
            row.find('.quantity-label').after(
                '<span class="stock-error text-danger form-text">Stok hanya ' + availableStock + ' tersedia!</span>'
            );
        } else {
            row.find('.quantity').removeClass('is-invalid');
            row.find('.stock-error').remove();
        }

        calculateSubtotal(row);
    });

    // Handle quantity change
    $(document).on('input', '.quantity', function() {
        const row = $(this).closest('.item-row');
        const select = row.find('.product-select');
        const selected = select.find('option:selected');
        const availableStock = parseInt(selected.data('stock')) || 0;
        const enteredQuantity = parseInt($(this).val()) || 0;

        // Set max attribute
        $(this).attr('max', availableStock);

        calculateSubtotal(row);
        validateQuantity(row);
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
                    <input type="number" name="items[${index}][sell_price]" class="form-control sell-price" step="0.01" min="0" readonly>
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
        $('#sale-items').append(newRow);
        populateProductSelect($('.product-select').last());
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        calculateTotal();
    });

    // Validate form before submit
    $('#sale-form').on('submit', function(e) {
        if (!validateAllItems()) {
            e.preventDefault();
            alert('Quantity salah untuk satu atau lebih item. Silakan sesuaikan dengan stok yang tersedia.');
            return false;
        }
    });

    // Initialize subtotals for existing items
    $('.item-row').each(function() {
        calculateSubtotal($(this));
    });
});
</script>
@endsection