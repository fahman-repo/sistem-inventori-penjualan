<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    <style>
        /* Dompdf-friendly font stack */
        @font-face {
            font-family: 'DejaVu Sans';
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.4;
        }

        /* Page setup for PDF */
        @page {
            margin: 15mm 10mm;
            size: A4 portrait;
        }

        .invoice-container {
            max-width: 750px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .company-info h1 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 22px;
            font-weight: bold;
        }

        .company-info p {
            margin: 4px 0;
            color: #666;
            font-size: 13px;
        }

        .invoice-info {
            text-align: right;
        }

        .invoice-info h2 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
            font-weight: bold;
        }

        .invoice-info p {
            margin: 6px 0;
            color: #666;
            font-size: 13px;
        }

        .customer-info {
            margin-bottom: 25px;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .customer-info h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }

        .customer-info p {
            margin: 4px 0;
            color: #555;
            font-size: 13px;
        }

        /* Improved table styling for Dompdf */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .invoice-table th,
        .invoice-table td {
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #333;
            font-size: 12px;
        }

        .invoice-table th {
            background: #333;
            color: #fff;
            font-weight: bold;
        }

        .invoice-table tr:nth-child(even) {
            background: #f5f5f5;
        }

        .invoice-table td.text-center {
            text-align: center;
        }

        .invoice-table td.text-right {
            text-align: right;
        }

        .invoice-summary {
            float: right;
            width: 280px;
            margin-bottom: 25px;
        }

        .invoice-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-summary td {
            padding: 8px 10px;
            border: 1px solid #333;
            font-size: 13px;
        }

        .invoice-summary td:first-child {
            text-align: left;
            font-weight: bold;
        }

        .invoice-summary td:last-child {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background: #f5f5f5;
        }

        .invoice-footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .invoice-footer p {
            margin: 5px 0;
        }

        /* Clear float */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1>🏬 Toko ABC</h1>
                <p>Jl. Contoh No. 123<br>Kota - Kode Pos</p>
                <p>Telpon: 0812-3456-7890</p>
            </div>
            <div class="invoice-info">
                <h2>INVOICE PENJUALAN</h2>
                <p><strong>Invoice #:</strong> {{ $sale->invoice_number }}</p>
                <p><strong>Tanggal:</strong> {{ $sale->sale_date->format('d F Y') }}</p>
                <p><strong>Kasir:</strong> {{ $sale->user->name }}</p>
            </div>
        </div>

        <div class="customer-info">
            <h3>Pelanggan / Item yang Dijual</h3>
            <p><strong>Transaksi atas nama:</strong> {{ $sale->user->name }}</p>
            <p><strong>Status:</strong> <span style="color: #28a745; font-weight: bold;">LUNAS</span></p>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 80px;">Kode</th>
                    <th>Nama Produk</th>
                    <th style="width: 60px; text-align: center;">Qty</th>
                    <th style="width: 100px; text-align: right;">Harga Jual</th>
                    <th style="width: 100px; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->sku }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-center">{{ $item->quantity }} {{ $item->product->unit }}</td>
                        <td class="text-right">Rp {{ number_format($item->sell_price, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="invoice-summary clearfix">
            <table>
                <tr>
                    <td style="width: 150px;">Subtotal Items</td>
                    <td style="width: 130px; text-align: right;">Rp {{ number_format($sale->items->sum('subtotal'), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pajak</td>
                    <td style="text-align: right;">Rp 0,00</td>
                </tr>
                <tr class="total-row">
                    <td>Total</td>
                    <td style="text-align: right;">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="invoice-footer">
            <p>Terima kasih atas pembelian Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
            <p>Invoice ini dicetak otomatis pada: {{ now()->format('d F Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>