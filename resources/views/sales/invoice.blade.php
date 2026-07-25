<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 12mm 10mm;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #2d3436;
            line-height: 1.5;
            background: #fff;
        }

        .invoice-container {
            max-width: 720px;
            margin: 0 auto;
            padding: 10px 0;
        }

        /* ===== HEADER ===== */
        .header {
            border-bottom: 3px solid #2d3436;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }

        .header-top {
            display: block;
            overflow: hidden;
        }

        .brand {
            float: left;
            width: 55%;
        }

        .brand h1 {
            font-size: 24px;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .brand p {
            font-size: 11px;
            color: #636e72;
            margin: 2px 0;
        }

        .invoice-badge {
            float: right;
            text-align: right;
            width: 45%;
        }

        .invoice-badge .label {
            display: inline-block;
            background: #2d3436;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 18px;
            letter-spacing: 1.5px;
            margin-bottom: 10px;
        }

        .invoice-badge table {
            margin-left: auto;
        }

        .invoice-badge table td {
            padding: 3px 0;
            font-size: 11px;
        }

        .invoice-badge table td:first-child {
            color: #636e72;
            padding-right: 15px;
            text-align: left;
        }

        .invoice-badge table td:last-child {
            font-weight: 600;
            color: #2d3436;
            text-align: right;
        }

        /* ===== META INFO ===== */
        .meta-section {
            width: 100%;
            margin-bottom: 22px;
            border-spacing: 12px 0;
        }

        .meta-section td {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }

        .meta-box {
            padding: 14px 16px;
            border: 1px solid #dfe6e9;
            background: #f8f9fa;
        }

        .meta-box h3 {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #636e72;
            margin: 0 0 10px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #dfe6e9;
        }

        .meta-box p {
            font-size: 11px;
            margin: 4px 0;
            color: #2d3436;
            line-height: 1.5;
        }

        .meta-box .highlight {
            font-weight: 700;
            color: #00b894;
        }

        /* ===== ITEMS TABLE ===== */
        .items-section {
            margin-bottom: 22px;
        }

        .items-section h3 {
            font-size: 13px;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #dfe6e9;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead th {
            background: #2d3436;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 10px;
            text-align: left;
            border: none;
        }

        .items-table thead th.num {
            text-align: center;
            width: 35px;
        }

        .items-table thead th.code {
            width: 85px;
        }

        .items-table thead th.qty {
            text-align: center;
            width: 70px;
        }

        .items-table thead th.price,
        .items-table thead th.subtotal {
            text-align: right;
            width: 105px;
        }

        .items-table tbody td {
            padding: 9px 10px;
            font-size: 11px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }

        .items-table tbody tr.even-row {
            background: #fafbfc;
        }

        .items-table tbody td.num {
            text-align: center;
            color: #636e72;
        }

        .items-table tbody td.qty {
            text-align: center;
        }

        .items-table tbody td.price,
        .items-table tbody td.subtotal {
            text-align: right;
        }

        .items-table tbody td.subtotal {
            font-weight: 600;
        }

        .product-name {
            font-weight: 600;
            color: #2d3436;
        }

        .product-unit {
            font-size: 10px;
            color: #636e72;
        }

        /* ===== SUMMARY ===== */
        .summary-section {
            margin-top: 30px;
            margin-bottom: 25px;
            border-top: 2px solid #2d3436;
            padding-top: 20px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            vertical-align: top;
        }

        .summary-table .spacer-cell {
            width: 60%;
        }

        .summary-box {
            width: 320px;
            margin-left: auto;
        }

        .summary-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-box table td {
            padding: 8px 10px;
            font-size: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        .summary-box table td.lbl {
            color: #636e72;
            text-align: left;
        }

        .summary-box table td.val {
            font-weight: 600;
            text-align: right;
        }

        .summary-box table tr.tax-detail td.lbl {
            font-size: 11px;
            color: #636e72;
        }

        .summary-box table tr.tax-detail td.val {
            font-size: 11px;
            color: #636e72;
            font-weight: 400;
        }

        .summary-box table tr.total-row td {
            border-bottom: none;
            border-top: 2px solid #2d3436;
            padding-top: 12px;
            padding-bottom: 4px;
            font-size: 16px;
            font-weight: 700;
            color: #2d3436;
        }

        /* ===== NOTES ===== */
        .notes-section {
            margin-bottom: 20px;
            padding: 10px 14px;
            background: #ffeaa7;
            border-left: 3px solid #fdcb6e;
            font-size: 11px;
            color: #2d3436;
        }

        /* ===== FOOTER ===== */
        .footer {
            border-top: 2px solid #2d3436;
            padding-top: 14px;
            text-align: center;
        }

        .footer p {
            font-size: 10px;
            color: #636e72;
            margin: 3px 0;
        }

        .footer .thanks {
            font-size: 13px;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 5px;
        }

        /* ===== UTILITIES ===== */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="invoice-container">

        <!-- HEADER -->
        <div class="header clearfix">
            <div class="brand">
                <h1>Toko ABC</h1>
                <p>Jl. Contoh No. 123</p>
                <p>Kota - Kode Pos</p>
                <p>Telp: 0812-3456-7890</p>
            </div>
            <div class="invoice-badge">
                <div class="label">INVOICE</div>
                <table>
                    <tr>
                        <td>No. Invoice</td>
                        <td>{{ $sale->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ $sale->sale_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Kasir</td>
                        <td>{{ $sale->user->name }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- META INFO -->
        <table class="meta-section">
            <tr>
                <td>
                    <div class="meta-box">
                        <h3>Diterbitkan Oleh</h3>
                        <p><strong>Toko ABC</strong></p>
                        <p>Jl. Contoh No. 123</p>
                        <p>Kota - Kode Pos</p>
                        <p>Telp: 0812-3456-7890</p>
                    </div>
                </td>
                <td>
                    <div class="meta-box">
                        <h3>Detail Transaksi</h3>
                        <p><strong>Kasir:</strong> {{ $sale->user->name }}</p>
                        <p><strong>Tanggal:</strong> {{ $sale->sale_date->format('d/m/Y') }}</p>
                        <p><strong>Status:</strong> <span class="highlight">LUNAS</span></p>
                        @if($sale->notes)
                            <p><strong>Catatan:</strong> {{ $sale->notes }}</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- ITEMS TABLE -->
        <div class="items-section">
            <h3>Detail Item Penjualan</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="num">#</th>
                        <th class="code">Kode</th>
                        <th>Nama Produk</th>
                        <th class="qty">Qty</th>
                        <th class="price">Harga Satuan</th>
                        <th class="subtotal">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                        <tr class="{{ $loop->even ? 'even-row' : '' }}">
                            <td class="num">{{ $index + 1 }}</td>
                            <td>{{ $item->product->sku }}</td>
                            <td>
                                <span class="product-name">{{ $item->product->name }}</span>
                            </td>
                            <td class="qty">{{ $item->quantity }} <span class="product-unit">{{ $item->product->unit }}</span></td>
                            <td class="price">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                            <td class="subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- SUMMARY -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td class="spacer-cell"></td>
                    <td>
                        <div class="summary-box">
                            <table>
                                <tr>
                                    <td class="lbl">Subtotal ({{ $sale->items->sum('quantity') }} item)</td>
                                    <td class="val">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                </tr>
                                @if($sale->tax)
                                    <tr class="tax-detail">
                                        <td class="lbl">{{ $sale->tax->name }} ({{ rtrim(rtrim(number_format($sale->tax->rate, 2, ',', '.'), '0'), ',') }}%)</td>
                                        <td class="val">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @else
                                    <tr class="tax-detail">
                                        <td class="lbl">Pajak</td>
                                        <td class="val">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr class="total-row">
                                    <td class="lbl">Grand Total</td>
                                    <td class="val">Rp {{ number_format($sale->total + $sale->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- NOTES -->
        @if($sale->notes)
            <div class="notes-section">
                <strong>Catatan:</strong> {{ $sale->notes }}
            </div>
        @endif

        <!-- FOOTER -->
        <div class="footer">
            <p class="thanks">Terima kasih atas pembelian Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
            <p>Dicetak pada: {{ now()->format('d F Y, H:i:s') }} &mdash; Dokumen ini dicetak secara otomatis oleh sistem.</p>
        </div>

    </div>
</body>
</html>
