@extends('adminlte::page')

@section('admin_title', 'Buat Penjualan Baru')

@section('content_header', 'Buat Penjualan Baru')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Penjualan</h3>
                    </div>
                    <div class="card-body">
                        <form id="sale-form" action="{{ route('sales.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="sale_date">Tanggal Penjualan</label>
                                <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ date('Y-m-d') }}" required>
                            </div>

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
                                <tbody id="purchase-items">
                                    <tr class="item-row">
                                        <td style="width: 40%;">
                                            <select name="items[0][product_id]" class="form-control product-select" required>
                                                <option value="">Pilih Produk</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="items[0][sell_price]" class="form-control sell-price" step="0.01" min="0" readonly>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="items[0][quantity]" class="form-control quantity" min="1" step="1" required>
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
                                </tbody>
                            </table>

                            <button type="button" id="add-item" class="btn btn-outline-primary mb-3">
                                <i class="fa fa-plus"></i> Tambah Item
                            </button>

                            <hr>

                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Total</label>
                                <p class="font-weight-bold">Rp <span id="total-amount">0</span></p>
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
    let itemCount = 1;

    function populateProductSelect(select) {
        let options = '<option value="">Pilih Produk</option>';
        products.forEach(function(p) {
            options += '<option value="' + p.id + '" data-price="' + p.sell_price + '" data-stock="' + p.stock + '">' +
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

    // Initialize first product select
    populateProductSelect($('.product-select').first());

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
                    <label class="quantity-label d-none"></label>
                    <input type="number" name="items[${index}][sell_price]" class="form-control sell-price" step="0.01" min="0" readonly>
                </td>
                <td class="text-center">
                    <label class="quantity-label d-none"></label>
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

    // Validate form before submit
    $('#sale-form').on('submit', function(e) {
        if (!validateAllItems()) {
            e.preventDefault();
            alert('Quantity salah untuk satu atau lebih item. Silakan sesuaikan dengan stok yang tersedia.');
            return false;
        }
    });
});
</script>
@endsection