@extends('adminlte::page')

@section('admin_title', 'Manajemen User')

@section('content_header', 'Manajemen User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismissal="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismissal="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah User
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search & Filter Form -->
                    <form method="GET" action="{{ route('users.index') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari user..."
                                   value="{{ request('search') }}" style="width: 250px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <select name="role" class="form-control mr-2" style="width: 200px;">
                            <option value="">Semua Role</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                        </select>
                        @if(request('search') || request('role'))
                            <a href="{{ route('users.index') }}" class="btn btn-outline-danger">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                    </form>

                    @if($users->count() > 0)
                    <table id="users-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge badge-success">Admin</span>
                                    @else
                                        <span class="badge badge-info">Kasir</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus user {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted">Tidak ada user yang ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#users-table').DataTable();
    });
</script>
@endsection