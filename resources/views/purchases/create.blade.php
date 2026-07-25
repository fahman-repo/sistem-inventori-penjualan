@extends('adminlte::page')

@section('admin_title', 'Buat Pembelian Baru')

@section('content_header', 'Buat Pembelian Baru')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Pembelian</h3>
                    </div>
                    <div class="card-body">
                        <form id="purchase-form" action="{{ route('purchases.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="supplier_id">Supplier</label>
                                <select class="form-control" id="supplier_id" name="supplier_id">
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="purchase_date">Tanggal Pembelian</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label>Status Pembayaran</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_status" id="payment_cash" value="cash" checked>
                                    <label class="form-check-label" for="payment_cash">
                                        <i class="fa fa-money text-success"></i> Cash (Lunas)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_status" id="payment_credit" value="credit">
                                    <label class="form-check-label" for="payment_credit">
                                        <i class="fa fa-credit-card text-warning"></i> Credit (Utang)
                                    </label>
                                </div>
                                @error('payment_status')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

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
                                    <tr class="item-row">
                                        <td style="width: 40%;">
                                            <select name="items[0][product_id]" class="form-control product-select" required>
                                                <option value="">Pilih Produk</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="items[0][buy_price]" class="form-control buy-price" step="0.01" min="0" readonly>
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
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="use_tax">
                                    <label class="custom-control-label" for="use_tax">Kena Pajak</label>
                                </div>
                            </div>

                            <div id="tax-section" style="display:none;">
                                <div class="form-group">
                                    <label for="tax-select">Pilih Pajak</label>
                                    <select name="tax_id" id="tax-select" class="form-control">
                                        <option value="">-- Pilih Pajak --</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="tax_amount" id="tax-amount-input" value="0">

                            <div class="form-group">
                                <label>Subtotal</label>
                                <p class="font-weight-bold">Rp <span id="subtotal-amount">0</span></p>
                            </div>
                            <div class="form-group">
                                <label>Pajak</label>
                                <p class="font-weight-bold">Rp <span id="tax-amount-display">0</span></p>
                            </div>
                            <div class="form-group">
                                <label>Grand Total</label>
                                <p class="font-weight-bold text-success" style="font-size: 1.2em;">Rp <span id="grand-total">0</span></p>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Simpan Pembelian
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
    let itemCount = 1;

    function populateProductSelect(select) {
        let options = '<option value="">Pilih Produk</option>';
        products.forEach(function(p) {
            options += '<option value="' + p.id + '" data-price="' + p.buy_price + '">' +
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
        $('#subtotal-amount').text('Rp ' + total.toLocaleString('id-ID'));
        calculateTax();
    }

    function calculateTax() {
        let subtotal = 0;
        $('.subtotal').each(function() {
            const val = parseFloat($(this).val()) || 0;
            subtotal += val;
        });
        let taxAmount = 0;
        if ($('#use_tax').is(':checked')) {
            const rate = parseFloat($('#tax-select option:selected').data('rate')) || 0;
            taxAmount = subtotal * (rate / 100);
        }
        $('#tax-amount-display').text('Rp ' + taxAmount.toLocaleString('id-ID'));
        $('#tax-amount-input').val(taxAmount);
        const grandTotal = subtotal + taxAmount;
        $('#grand-total').text('Rp ' + grandTotal.toLocaleString('id-ID'));
    }

    // Initialize first product select
    populateProductSelect($('.product-select').first());

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

    // Tax checkbox toggle
    $('#use_tax').change(function() {
        if ($(this).is(':checked')) {
            $('#tax-section').show();
        } else {
            $('#tax-section').hide();
            $('#tax-select').val('');
        }
        calculateTax();
    });

    // Tax select change
    $(document).on('change', '#tax-select', function() {
        calculateTax();
    });
});
</script>
@endsection