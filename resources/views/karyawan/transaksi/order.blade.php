@extends('layouts.backend')
@section('title','Dashboard Karyawan')
@section('content')
@if ($message = Session::get('success'))
  <div class="alert alert-success alert-block">
  <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
  </div>
@elseif ($message = Session::get('error'))
  <div class="alert alert-danger alert-block">
  <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{{ $message }}</strong>
  </div>
@endif

<!-- Modal Loading -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5 class="mt-3">Memproses...</h5>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Daftar Transaksi Laundry</h4>
            <a href="{{url('add-order')}}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Order Baru
            </a>
        </div>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Info:</strong>
            <ul class="mb-0 mt-1">
                <li>Klik tombol <span class="badge badge-success">Bayar</span> untuk mengubah status pembayaran menjadi Lunas</li>
                <li>Klik tombol <span class="badge badge-info">Selesai</span> untuk menyelesaikan proses laundry</li>
                <li>Klik tombol <span class="badge badge-primary">Diambil</span> (tersedia setelah Lunas) untuk menandai laundry sudah diambil customer</li>
                <li>Klik tombol <span class="badge badge-warning">Invoice</span> untuk melihat dan mencetak invoice</li>
                <li><strong>Data diurutkan dari yang TERBARU (paling atas)</strong></li>
            </ul>
        </div>

        <div class="table-responsive">
            <table id="myTable" class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%">#</th>
                        <th width="12%">No Resi</th>
                        <th width="10%">Tgl Transaksi</th>
                        <th width="12%">Customer</th>
                        <th width="10%">Status Laundry</th>
                        <th width="8%">Payment</th>
                        <th width="10%">Jenis</th>
                        <th width="10%">Total</th>
                        <th width="23%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($order as $item)
                    <tr id="row-{{$item->id}}">
                        <td>{{ $no++ }}</td>
                        <td>
                            <strong>{{ $item->invoice }}</strong>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($item->tgl_transaksi)->format('d/m/Y H:i') }}</td>
                        <td>
                            <strong>{{ $item->customer }}</strong>
                            @if($item->customers)
                                <br>
                                <small class="text-muted">{{ $item->customers->no_telp ?? '-' }}</small>
                            @endif
                        </td>
                        <td>
                            @if ($item->status_order == 'Process')
                                <span class="badge badge-info">Diproses</span>
                            @elseif($item->status_order == 'Done')
                                <span class="badge badge-success">Selesai</span>
                            @elseif($item->status_order == 'Delivery')
                                <span class="badge badge-primary">Diambil</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->status_payment == 'Success')
                                <span class="badge badge-success">Lunas</span>
                            @elseif($item->status_payment == 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>{{ $item->price->jenis ?? '-' }}</td>
                        <td>
                            <strong>{{ Rupiah::getRupiah($item->harga_akhir) }}</strong>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <!-- Tombol Bayar - Hanya tampil jika status payment Pending -->
                                @if($item->status_payment == 'Pending')
                                    <button class="btn btn-sm btn-success btn-bayar"
                                            data-id="{{ $item->id }}"
                                            data-invoice="{{ $item->invoice }}"
                                            data-customer="{{ $item->customer }}"
                                            data-status-payment="{{ $item->status_payment }}"
                                            title="Ubah status pembayaran menjadi Lunas">
                                        <i class="fa fa-money"></i> Bayar
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled title="Pembayaran sudah Lunas">
                                        <i class="fa fa-money"></i> Bayar
                                    </button>
                                @endif

                                <!-- Tombol Invoice - Selalu tampil -->
                                <a href="{{ url('invoice-kar', $item->id) }}"
                                   class="btn btn-sm btn-warning"
                                   target="_blank"
                                   title="Lihat Invoice">
                                    <i class="fa fa-file-pdf-o"></i> Invoice
                                </a>

                                <!-- Tombol Selesai - Selalu tampil untuk status Process -->
                                @if($item->status_order == 'Process')
                                    <button class="btn btn-sm btn-info btn-selesai"
                                            data-id="{{ $item->id }}"
                                            data-invoice="{{ $item->invoice }}"
                                            data-customer="{{ $item->customer }}"
                                            data-status-payment="{{ $item->status_payment }}"
                                            data-status-order="{{ $item->status_order }}"
                                            data-action="selesai"
                                            title="Selesaikan proses laundry">
                                        <i class="fa fa-check"></i> Selesai
                                    </button>
                                @endif

                                <!-- Tombol Diambil - Hanya tampil jika status Done DAN Payment Success -->
                                @if($item->status_order == 'Done' && $item->status_payment == 'Success')
                                    <button class="btn btn-sm btn-primary btn-selesai"
                                            data-id="{{ $item->id }}"
                                            data-invoice="{{ $item->invoice }}"
                                            data-customer="{{ $item->customer }}"
                                            data-status-payment="{{ $item->status_payment }}"
                                            data-status-order="{{ $item->status_order }}"
                                            data-action="diambil"
                                            title="Tandai laundry sudah diambil customer">
                                        <i class="fa fa-truck"></i> Diambil
                                    </button>
                                @endif

                                <!-- Status Done tapi Belum Bayar - Tampilkan pesan -->
                                @if($item->status_order == 'Done' && $item->status_payment == 'Pending')
                                    <button class="btn btn-sm btn-secondary" disabled title="Customer harus bayar terlebih dahulu">
                                        <i class="fa fa-clock-o"></i> Tunggu Bayar
                                    </button>
                                @endif

                                <!-- Status Delivery - Tombol disabled -->
                                @if($item->status_order == 'Delivery')
                                    <button class="btn btn-sm btn-secondary" disabled title="Transaksi sudah selesai">
                                        <i class="fa fa-check-circle"></i> Selesai
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-muted">
            <small><i class="fa fa-clock-o"></i> Total {{ count($order) }} transaksi</small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // DATATABLE - TANPA MENGURUTKAN (karena sudah diurutkan dari controller)
    $('#myTable').DataTable({
        "pageLength": 25,
        "order": [], // Nonaktifkan sorting default
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8] } // Semua kolom tidak bisa diurutkan
        ]
    });

    // Handle klik tombol Bayar
    $(document).on('click', '.btn-bayar:not(:disabled)', function() {
        var id = $(this).data('id');
        var invoice = $(this).data('invoice');
        var customer = $(this).data('customer');

        Swal.fire({
            title: 'Konfirmasi Pembayaran',
            html: `
                <div style="text-align: left;">
                    <p>Apakah Anda yakin ingin mengubah status pembayaran menjadi <strong>LUNAS</strong> untuk transaksi berikut:</p>
                    <table style="margin: 10px auto; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 5px;"><strong>Invoice</strong></td>
                            <td style="padding: 5px;">:</td>
                            <td style="padding: 5px;">${invoice}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px;"><strong>Customer</strong></td>
                            <td style="padding: 5px;">:</td>
                            <td style="padding: 5px;">${customer}</td>
                        </tr>
                    </table>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Bayar!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                $('#loadingModal').modal('show');

                $.ajax({
                    url: '{{ url("update-status-laundry") }}',
                    type: 'GET',
                    data: {
                        'id': id,
                        'action': 'bayar'
                    },
                    success: function(response) {
                        $('#loadingModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status pembayaran berhasil diubah menjadi Lunas',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        var errorMsg = 'Terjadi kesalahan';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Handle klik tombol Selesai / Diambil
    $(document).on('click', '.btn-selesai:not(:disabled)', function() {
        var id = $(this).data('id');
        var invoice = $(this).data('invoice');
        var customer = $(this).data('customer');
        var statusPayment = $(this).data('status-payment');
        var action = $(this).data('action'); // 'selesai' atau 'diambil'

        var title = '';
        var actionText = '';
        var confirmButtonColor = '';

        if (action === 'selesai') {
            title = 'Konfirmasi Penyelesaian Laundry';
            actionText = 'menyelesaikan';
            confirmButtonColor = '#17a2b8';
        } else if (action === 'diambil') {
            title = 'Konfirmasi Pengambilan Laundry';
            actionText = 'mengambil';
            confirmButtonColor = '#007bff';
        }

        var paymentWarning = '';
        if (statusPayment == 'Pending' && action === 'selesai') {
            paymentWarning = '<p style="color: #ffc107; margin-top: 15px;"><i class="fa fa-exclamation-triangle"></i> <strong>Catatan:</strong> Status pembayaran masih Pending. Customer akan membayar saat mengambil laundry.</p>';
        }

        Swal.fire({
            title: title,
            html: `
                <div style="text-align: left;">
                    <p>Apakah Anda yakin ingin ${actionText} laundry untuk transaksi berikut:</p>
                    <table style="margin: 10px auto; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 5px;"><strong>Invoice</strong></td>
                            <td style="padding: 5px;">:</td>
                            <td style="padding: 5px;">${invoice}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px;"><strong>Customer</strong></td>
                            <td style="padding: 5px;">:</td>
                            <td style="padding: 5px;">${customer}</td>
                        </tr>
                    </table>
                    ${paymentWarning}
                </div>
            `,
            icon: (statusPayment == 'Pending' && action === 'selesai') ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: confirmButtonColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Proses!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                $('#loadingModal').modal('show');

                $.ajax({
                    url: '{{ url("update-status-laundry") }}',
                    type: 'GET',
                    data: {
                        'id': id,
                        'action_type': action
                    },
                    success: function(response) {
                        $('#loadingModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Status laundry berhasil diubah!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        $('#loadingModal').modal('hide');
                        var errorMsg = 'Terjadi kesalahan';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });
});
</script>

<style>
.btn {
    margin: 2px;
}
.btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.badge {
    display: inline-block;
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: black;
}

.badge-primary {
    background-color: #007bff;
    color: white;
}

/* Table styling */
.table thead th {
    background-color: #343a40;
    color: white;
    border-color: #454d55;
    font-weight: 600;
    vertical-align: middle;
}

.table tbody td {
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

/* Modal Loading */
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

.spinner-border {
    display: inline-block;
    width: 3rem;
    height: 3rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

/* Alert styling */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.alert .close {
    float: right;
    font-size: 21px;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: .2;
}

.alert ul {
    padding-left: 20px;
}

/* SweetAlert2 Custom Styles */
.swal2-popup {
    font-family: inherit;
    border-radius: 10px;
    padding: 1.5em;
}

.swal2-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
}

.swal2-html-container {
    font-size: 1rem;
    line-height: 1.6;
    color: #555;
}

.swal2-confirm {
    border-radius: 5px !important;
    padding: 10px 20px !important;
    font-weight: 500 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.swal2-cancel {
    border-radius: 5px !important;
    padding: 10px 20px !important;
    font-weight: 500 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.swal2-icon {
    margin: 1em auto;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    .btn-sm {
        width: 100%;
        margin: 2px 0;
    }
}
</style>
@endsection
