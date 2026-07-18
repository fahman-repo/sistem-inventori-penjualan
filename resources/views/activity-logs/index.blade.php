@extends('adminlte::page')

@section('title', 'Activity Log')

@section('content_header')
    <h1>Activity Log</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Aktivitas</h3>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('activity-logs.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">-- Semua User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Dari Tanggal</label>
                            <input type="date" name="date_from" id="date_from" class="form-control"
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Sampai Tanggal</label>
                            <input type="date" name="date_to" id="date_to" class="form-control"
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th width="180">Waktu</th>
                            <th width="150">User</th>
                            <th width="100">Aksi</th>
                            <th width="120">Model</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match ($log->action) {
                                            'create' => 'badge-success',
                                            'update' => 'badge-primary',
                                            'delete' => 'badge-danger',
                                            'stock_opname' => 'badge-warning',
                                            default => 'badge-secondary',
                                        };
                                        $actionLabel = match ($log->action) {
                                            'create' => 'Tambah',
                                            'update' => 'Ubah',
                                            'delete' => 'Hapus',
                                            'stock_opname' => 'Stock Opname',
                                            default => $log->action,
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $actionLabel }}</span>
                                </td>
                                <td>{{ $log->model_type }}</td>
                                <td>{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <em>Belum ada aktivitas.</em>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@stop