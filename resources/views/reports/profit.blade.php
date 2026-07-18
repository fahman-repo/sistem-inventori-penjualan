@extends('adminlte::page')

@section('admin_title', 'Laporan Laba Kotor')

@section('content_header', 'Laporan Laba Kotor')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-chart-pie mr-2"></i>Laporan Laba Kotor</h3>
                </div>
                <div class="card-body">
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fa fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Total Penjualan</span>
                                    <span class="info-box-content">Rp {{ number_format($totalSell, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fa fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Total Beli</span>
                                    <span class="info-box-content">Rp {{ number_format($totalBuy, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fa fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-title">Laba Kotor</span>
                                    <span class="info-box-content">Rp {{ number_format($totalProfit, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('reports.profit') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}" placeholder="Dari">
                        </div>
                        <div class="input-group mr-2">
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}" placeholder="Sampai">
                        </div>
                        @if(request('date_from') || request('date_to') || request('category_id'))
                            <a href="{{ route('reports.profit') }}" class="btn btn-outline-danger mr-2">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif()
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                    </form>

                    @if(request('date_from') || request('date_to'))
                        <div class="alert alert-info" role="alert">
                            <strong>Filter:</strong>
                            @if(request('date_from'))
                                {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }}
                            @else
                                –
                            @endif
                            s/d
                            @if(request('date_to'))
                                {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}
                            @else
                                –
                            @endif()
                        </div>
                    @endif()

                    <!-- Profit Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fa fa-chart-bar mr-2"></i>Laba Kotor 7 Hari Terakhir</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="profitChart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profit by Product Table -->
                    <h5>Laba Kotor per Produk</h5>
                    @if($profitByProduct->count() > 0)
                        <table id="profit-table" class="table table-bordered table-hover dataTable" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty Terjual</th>
                                    <th>Penjualan</th>
                                    <th>Beli (asal)</th>
                                    <th>Laba Kotor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profitByProduct as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            <br>
                                            <small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        </td>
                                        <td>{{ $item->total_quantity }}</td>
                                        <td>Rp {{ number_format($item->total_sell, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->total_buy, 0, ',', '.') }}</td>
                                        <td>
                                            @if($item->total_profit > 0)
                                                <span class="text-success font-weight-bold">
                                                    + Rp {{ number_format($item->total_profit, 0, ',', '.') }}
                                                </span>
                                            @elseif($item->total_profit < 0)
                                                <span class="text-danger font-weight-bold">
                                                    Rp {{ number_format(abs($item->total_profit), 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif()
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">Tidak ada data penjualan yang ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js"></script>
<script>
    // Profit Chart
    const profitChartCtx = document.getElementById('profitChart').getContext('2d');
    const profitChart = new Chart(profitChartCtx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Laba Kotor (Rp)',
                data: @json($chartData['data']),
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

    // DataTable for profit table
    $('#profit-table').DataTable();
</script>
@endsection