@extends('adminlte::page')

@section('admin_title', 'Daftar Pelanggan')

@section('content_header', 'Daftar Pelanggan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Tambah Pelanggan
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('customers.index') }}" class="form-inline mb-3">
                        <div class="input-group mr-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, telepon, atau email..."
                                   value="{{ request('search') }}" style="width: 280px;">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('customers.index') }}" class="btn btn-outline-danger">
                                <i class="fa fa-times"></i> Reset
                            </a>
                        @endif
                    </form>

                    @if($customers->count() > 0)
                    <table id="customers-table" class="table table-bordered table-hover dataTable" style="width:100%">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Jumlah Penjualan</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                            <tr>
                                <td>{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>{{ $customer->sales_count }}</td>
                                <td>{{ $customer->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $customers->links() }}
                    </div>
                    @else
                    <p class="text-muted">Tidak ada pelanggan yang ditemukan.</p>
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
        $('#customers-table').DataTable();
    });
</script>
@endsection
