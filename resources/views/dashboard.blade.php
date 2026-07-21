@extends('adminlte::page')

@section('admin_title', 'Dashboard')

@section('content_header', 'Dashboard')

@section('content')
<div class="container-fluid">
    @if(auth()->user()->role === 'admin')
        <!-- Admin Dashboard -->
        <div class="row">
            <!-- Summary Cards -->
            <div class="col-lg-3 col-md-6">
                <div class="info-box mb-4">
                    <span class="info-box-icon bg-success"><i class="fa fa-boxes"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-title">Total Produk</span>
                        <span class="info-box-content">{{ $totalProducts }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box mb-4">
                    <span class="info-box-icon bg-info"><i class="fa fa-truck-loading"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-title">Total Stok</span>
                        <span class="info-box-content">{{ $totalStock }} pcs</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box mb-4">
                    <span class="info-box-icon bg-warning"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-title">Stok Menipis</span>
                        <span class="info-box-content">{{ $lowStockProducts }} item</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box mb-4">
                    <span class="info-box-icon bg-danger"><i class="fa fa-ban"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-title">Habis</span>
                        <span class="info-box-content">{{ $outOfStock }} item</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Sales Card -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fa fa-calendar-day"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-title">Penjualan Hari Ini</span>
                        <span class="info-box-content">Rp {{ number_format($todaySales, 0, ',', '.') }}</span>
                        <div class="progress">
                            <div class="progress-bar" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supplier Debt Warning Widget -->
        @if($totalDueDebts > 0)
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalDueDebts }}</h3>
                        <p>Utang {{ $totalDueDebts > 1 ? 'Mendekati/Meledas' : 'Mendekati/Meledas' }} Jatuh Tempo</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-hand-holding-usd"></i>
                    </div>
                    <a href="{{ route('supplier-debts.index') }}" class="small-box-footer">
                        Lihat Semua <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>Rp {{ number_format($totalDueAmount, 0, ',', '.') }}</h3>
                        <p>Total Sisa Utang Jatuh Tempo</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('supplier-debts.index', ['status' => 'unpaid']) }}" class="small-box-footer">
                        Lihat Utang Aktif <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Debt List Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-hand-holding-usd mr-2"></i>Utang Supplier Perlu Perhatian</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Supplier</th>
                                    <th>Sisa Utang</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dueDebts as $debt)
                                <tr>
                                    <td>{{ $debt->supplier->name ?? '-' }}</td>
                                    <td class="font-weight-bold text-danger">
                                        Rp {{ number_format($debt->remaining_amount, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if($debt->due_date)
                                            @if($debt->due_date < now())
                                                <span class="text-danger font-weight-bold">
                                                    {{ $debt->due_date->format('d/m/Y') }}
                                                    <i class="fa fa-exclamation-circle" title="Terlambat"></i>
                                                </span>
                                            @else
                                                {{ $debt->due_date->format('d/m/Y') }}
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $debt->status_badge }}">
                                            {{ $debt->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('supplier-debts.show', $debt->id) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('supplier-debts.index') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-hand-holding-usd"></i> Lihat Semua Utang
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Sales Chart -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-chart-line mr-2"></i>Penjualan 7 Hari Terakhir</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-chart-pie mr-2"></i>Laba Kotor 7 Hari Terakhir</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="profitChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fa fa-clock mr-2"></i>Transaksi Terakhir</h3>
                    </div>
                    <div class="card-body">
                        @if($recentSales->count() > 0)
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSales as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_number }}</td>
                                            <td>{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                                            <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                            <td><span class="badge badge-success">Selesai</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">Belum ada transaksi penjualan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Kasir Dashboard -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Panel Kasir</h3>
                    </div>
                    <div class="card-body">
                        <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>!</p>
                        <p>Anda adalah <strong>Kasir</strong>. Silakan buat transaksi penjualan baru.</p>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <a href="{{ route('sales.create') }}" class="btn btn-success btn-block">
                                    <i class="fa fa-cash-register"></i> Buat Penjualan Baru
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('sales.index') }}" class="btn btn-info btn-block">
                                    <i class="fa fa-history"></i> Riwayat Penjualan
                                </a>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-lg-3 col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fa fa-cash-register"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-title">Penjualan Hari Ini</span>
                                        <span class="info-box-content">Rp {{ number_format($todaySales, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fa fa-file-invoice"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-title">Transaksi Hari Ini</span>
                                        <span class="info-box-content">{{ $todaySalesCount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fa fa-chart-line mr-2"></i>Penjualan 7 Hari Terakhir</h3>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="salesChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('js')
<script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js"></script>
<script>
    // Sales Chart
    const salesChartCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesChartCtx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Penjualan (Rp)',
                data: @json($chartData['data']),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }]
            }
        }
    });

    // Profit Chart
    const profitChartCtx = document.getElementById('profitChart').getContext('2d');
    const profitChart = new Chart(profitChartCtx, {
        type: 'bar',
        data: {
            labels: @json($profitData['labels']),
            datasets: [{
                label: 'Laba Kotor (Rp)',
                data: @json($profitData['data']),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }]
            }
        }
    });
</script>
@endsection