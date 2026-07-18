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