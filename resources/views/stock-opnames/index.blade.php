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
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
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
                                @forelse ($stockOpnames as $opname)
                                    @php
                                        $adjustedCount = $opname->items->filter(fn($i) => $i->difference != 0)->count();
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">
                                            <a href="{{ route('stock-opnames.show', $opname) }}">
                                                {{ $opname->opname_number }}
                                            </a>
                                        </td>
                                        <td>{{ $opname->opname_date->format('d/m/Y') }}</td>
                                        <td>{{ $opname->user->name ?? '-' }}</td>
                                        <td class="text-center">{{ $opname->items->count() }}</td>
                                        <td class="text-center">
                                            @if ($adjustedCount > 0)
                                                <span class="text-warning font-weight-bold">{{ $adjustedCount }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>{{ $opname->notes ?? '-' }}</td>
                                        <td>{{ $opname->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <em>Belum ada stock opname.</em>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $stockOpnames->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop