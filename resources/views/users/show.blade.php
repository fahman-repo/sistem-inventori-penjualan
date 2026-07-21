@extends('adminlte::page')

@section('admin_title', 'Detail User')

@section('content_header', 'Detail User')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi User</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Nama</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-success">Admin</span>
                                @else
                                    <span class="badge badge-info">Kasir</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Diperbarui</th>
                            <td>{{ $user->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit User
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection