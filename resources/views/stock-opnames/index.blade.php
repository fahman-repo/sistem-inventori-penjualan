@extends('adminlte::page')

@section('title', 'Stock Opname')

@section('content_header')
    <h1>Stock Opname</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Stock Opname</h3>
                    <div class="card-tools">
                        <a href="{{ route('stock-opnames.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Stock Opname Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('stock-opnames.index') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}" aria-label="Dari tanggal">
                        </div>
                        <div class="input-group mr-2">
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}" aria-label="Sampai tanggal">
                        </div>
                        @if(request('date_from') || request('date_to'))
                            <a href="{{ route('stock-opnames.index') }}" class="btn btn-outline-danger mr-2">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        @endif
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </form>

                    <div class="table-responsive">
                        <table id="stock-opnames-table" class="table table-bordered table-hover table-striped dataTable" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="160">No. Opname</th>
                                    <th width="120">Tanggal</th>
                                    <th width="150">User</th>
                                    <th width="80" class="text-center">Total Item</th>
                                    <th width="100" class="text-center">Disesuaikan</th>
                                    <th>Catatan</th>
                                    <th width="160">Dibuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockOpnames as $opname)
                                    <tr>
                                        <td class="font-weight-bold">
                                            <a href="{{ route('stock-opnames.show', $opname) }}">
                                                {{ $opname->opname_number }}
                                            </a>
                                        </td>
                                        <td data-order="{{ $opname->opname_date->format('Y-m-d') }}">
                                            {{ $opname->opname_date->format('d/m/Y') }}
                                        </td>
                                        <td>{{ $opname->user->name ?? '-' }}</td>
                                        <td class="text-center">{{ $opname->items_count }}</td>
                                        <td class="text-center">
                                            @if ($opname->adjusted_items_count > 0)
                                                <span class="text-warning font-weight-bold">{{ $opname->adjusted_items_count }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>{{ $opname->notes ?? '-' }}</td>
                                        <td data-order="{{ $opname->created_at->format('Y-m-d H:i:s') }}">
                                            {{ $opname->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#stock-opnames-table').DataTable({
            order: [[6, 'desc']],
            pageLength: 15
        });
    });
</script>
@endsection