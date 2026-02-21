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

<!-- Modal Notifikasi -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="notificationModalLabel">
                    <i class="fa fa-check-circle"></i> Notifikasi Sukses
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fa fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 id="notificationMessage" class="mb-3">Pembayaran Berhasil!</h4>
                <p id="notificationDetail" class="text-muted">Status telah diperbarui</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" data-dismiss="modal">
                    <i class="fa fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="position-fixed top-0 right-0 p-3" style="z-index: 9999; right: 0; top: 70px;">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-autohide="false">
        <div class="toast-header bg-success text-white">
            <i class="fa fa-check-circle mr-2"></i>
            <strong class="mr-auto" id="toastTitle">Berhasil</strong>
            <small>baru saja</small>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="toastMessage">
            Pembayaran berhasil diproses
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Daftar Transaksi Laundry</h4>
            <div>
                <button class="btn btn-info mr-2" onclick="refreshData()">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
                <a href="{{url('add-order')}}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Tambah Order Baru
                </a>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Info:</strong>
            <ul class="mb-0 mt-1">
                <li>Klik tombol <span class="badge badge-success">Bayar</span> untuk mengubah status pembayaran menjadi Lunas</li>
                <li>Klik tombol <span class="badge badge-info">Selesai</span> untuk menyelesaikan proses laundry</li>
                <li>Klik tombol <span class="badge badge-primary">Diambil</span> (tersedia setelah Lunas) untuk menandai laundry sudah diambil customer</li>
                <li>Klik tombol <span class="badge badge-warning">Invoice</span> untuk melihat dan mencetak invoice</li>
                <li><strong>Setelah Bayar, akan muncul opsi untuk lanjut ke proses Selesai/Diambil</strong></li>
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
                        <td class="status-order-{{$item->id}}">
                            @if ($item->status_order == 'Process')
                                <span class="badge badge-info">Diproses</span>
                            @elseif($item->status_order == 'Done')
                                <span class="badge badge-success">Selesai</span>
                            @elseif($item->status_order == 'Delivery')
                                <span class="badge badge-primary">Diambil</span>
                            @endif
                        </td>
                        <td class="status-payment-{{$item->id}}">
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
                        <td class="action-buttons-{{$item->id}}">
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

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Konfigurasi Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // DATATABLE
    var table = $('#myTable').DataTable({
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
        var btnBayar = $(this);

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
                    <p class="text-success mt-2"><i class="fa fa-info-circle"></i> Setelah pembayaran, Anda dapat langsung melanjutkan ke proses berikutnya.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-money"></i> Ya, Bayar!',
            cancelButtonText: '<i class="fa fa-times"></i> Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '{{ url("update-status-laundry") }}',
                    type: 'GET',
                    data: {
                        'id': id,
                        'action': 'bayar'
                    },
                    beforeSend: function() {
                        // Nonaktifkan tombol
                        btnBayar.prop('disabled', true);
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    // Aktifkan kembali tombol jika error
                    btnBayar.prop('disabled', false);
                    let errorMsg = 'Terjadi kesalahan';
                    try {
                        if (error.responseJSON && error.responseJSON.message) {
                            errorMsg = error.responseJSON.message;
                        } else if (error.responseText) {
                            errorMsg = error.responseText;
                        }
                    } catch(e) {
                        errorMsg = 'Gagal memproses pembayaran';
                    }
                    Swal.showValidationMessage(errorMsg);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Update tampilan status pembayaran
                $(`.status-payment-${id}`).html('<span class="badge badge-success">Lunas</span>');

                // Update tombol Bayar menjadi disabled
                $(`.btn-bayar[data-id="${id}"]`).replaceWith(`
                    <button class="btn btn-sm btn-secondary" disabled title="Pembayaran sudah Lunas">
                        <i class="fa fa-money"></i> Bayar
                    </button>
                `);

                // Tampilkan notifikasi sukses dengan opsi lanjutan
                toastr.success('Pembayaran berhasil diproses!', 'Sukses');

                // Tanyakan apakah ingin lanjut ke proses berikutnya
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    html: `
                        <div style="text-align: center;">
                            <div class="mb-3">
                                <i class="fa fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <p>Status pembayaran invoice <strong>${invoice}</strong> telah menjadi <strong class="text-success">LUNAS</strong>.</p>
                            <p class="mt-3 mb-0"><strong>Apa yang ingin Anda lakukan selanjutnya?</strong></p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#007bff',
                    confirmButtonText: '<i class="fa fa-check"></i>Laundry Selesai',
                    cancelButtonText: '<i class="fa fa-truck"></i> Langsung Diambil',
                    showDenyButton: true,
                    denyButtonText: '<i class="fa fa-clock-o"></i> Nanti Saja',
                    denyButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((nextResult) => {
                    if (nextResult.isConfirmed) {
                        // Jika user memilih "Selesai Laundry"
                        prosesSelesaiLaundry(id, invoice, customer);
                    } else if (nextResult.isDenied) {
                        // Jika user memilih "Nanti Saja"
                        toastr.info('Anda dapat melanjutkan proses nanti', 'Info');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else if (nextResult.dismiss === Swal.DismissReason.cancel) {
                        // Jika user memilih "Langsung Diambil"
                        prosesAmbilLaundry(id, invoice, customer);
                    }
                });
            }
        });
    });

    // Fungsi untuk memproses selesai laundry
    function prosesSelesaiLaundry(id, invoice, customer) {
        Swal.fire({
            title: 'Konfirmasi Penyelesaian Laundry',
            html: `
                <div style="text-align: left;">
                    <p>Apakah Anda ingin langsung <strong>MENYELESAIKAN</strong> laundry untuk:</p>
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
                    <p class="text-info mt-2"><i class="fa fa-info-circle"></i> Dengan menyelesaikan laundry, status akan berubah menjadi <strong>SELESAI</strong> dan siap diambil.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-check"></i> Ya, Selesaikan!',
            cancelButtonText: '<i class="fa fa-times"></i> Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '{{ url("update-status-laundry") }}',
                    type: 'GET',
                    data: {
                        'id': id,
                        'action_type': 'selesai'
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    let errorMsg = 'Terjadi kesalahan';
                    try {
                        if (error.responseJSON && error.responseJSON.message) {
                            errorMsg = error.responseJSON.message;
                        }
                    } catch(e) {
                        errorMsg = 'Gagal memproses';
                    }
                    Swal.showValidationMessage(errorMsg);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Update status order
                $(`.status-order-${id}`).html('<span class="badge badge-success">Selesai</span>');

                toastr.success('Laundry selesai diproses!', 'Sukses');

                Swal.fire({
                    icon: 'success',
                    title: 'Laundry Selesai!',
                    html: `
                        <div style="text-align: center;">
                            <p>Status laundry invoice <strong>${invoice}</strong> telah menjadi <strong class="text-success">SELESAI</strong>.</p>
                            <p class="mt-3"><strong>Apakah laundry langsung diambil customer?</strong></p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-truck"></i> Ya, Diambil',
                    cancelButtonText: '<i class="fa fa-clock-o"></i> Nanti',
                    reverseButtons: true
                }).then((ambilResult) => {
                    if (ambilResult.isConfirmed) {
                        prosesAmbilLaundry(id, invoice, customer);
                    } else {
                        toastr.info('Status laundry: Selesai (menunggu diambil)', 'Info');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                });
            }
        });
    }

    // Fungsi untuk memproses pengambilan laundry
    function prosesAmbilLaundry(id, invoice, customer) {
        Swal.fire({
            title: 'Konfirmasi Pengambilan Laundry',
            html: `
                <div style="text-align: left;">
                    <p>Apakah Anda yakin ingin menandai laundry <strong>DIAMBIL</strong> untuk:</p>
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
                    <p class="text-primary mt-2"><i class="fa fa-info-circle"></i> Dengan menandai DIAMBIL, transaksi akan selesai sepenuhnya.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-check"></i> Ya, Diambil!',
            cancelButtonText: '<i class="fa fa-times"></i> Batal',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '{{ url("update-status-laundry") }}',
                    type: 'GET',
                    data: {
                        'id': id,
                        'action_type': 'diambil'
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    let errorMsg = 'Terjadi kesalahan';
                    try {
                        if (error.responseJSON && error.responseJSON.message) {
                            errorMsg = error.responseJSON.message;
                        }
                    } catch(e) {
                        errorMsg = 'Gagal memproses';
                    }
                    Swal.showValidationMessage(errorMsg);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Update status order
                $(`.status-order-${id}`).html('<span class="badge badge-primary">Diambil</span>');

                // Sembunyikan tombol aksi
                $(`.action-buttons-${id}`).find('button:not(.btn-warning)').remove();
                $(`.action-buttons-${id}`).find('.btn-group').append(`
                    <button class="btn btn-sm btn-secondary" disabled title="Transaksi sudah selesai">
                        <i class="fa fa-check-circle"></i> Selesai
                    </button>
                `);

                // Tampilkan notifikasi sukses
                toastr.success('Transaksi laundry selesai!', 'Sukses');

                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Selesai!',
                    html: `
                        <div style="text-align: center;">
                            <i class="fa fa-check-circle text-success" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                            <p>Laundry dengan invoice <strong>${invoice}</strong> telah <strong>DIAMBIL</strong> customer.</p>
                            <p class="text-success">Transaksi selesai!</p>
                        </div>
                    `,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: '<i class="fa fa-check"></i> OK'
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    // Handle klik tombol Selesai / Diambil (manual)
    $(document).on('click', '.btn-selesai:not(:disabled)', function() {
        var id = $(this).data('id');
        var invoice = $(this).data('invoice');
        var customer = $(this).data('customer');
        var statusPayment = $(this).data('status-payment');
        var action = $(this).data('action'); // 'selesai' atau 'diambil'
        var btn = $(this);

        if (action === 'selesai') {
            prosesSelesaiLaundry(id, invoice, customer);
        } else if (action === 'diambil') {
            prosesAmbilLaundry(id, invoice, customer);
        }
    });

    // Fungsi refresh data tanpa reload halaman
    window.refreshData = function() {
        $('#loadingModal').modal('show');

        $.ajax({
            url: window.location.href,
            type: 'GET',
            success: function(response) {
                // Refresh halaman setelah 1 detik
                setTimeout(() => {
                    $('#loadingModal').modal('hide');
                    location.reload();
                }, 1000);
            },
            error: function() {
                $('#loadingModal').modal('hide');
                toastr.error('Gagal refresh data', 'Error');
            }
        });
    };

    // Auto refresh setiap 5 menit (300000 ms)
    setInterval(function() {
        refreshData();
    }, 300000);
});
</script>

<style>
/* Button Styles */
.btn {
    margin: 2px;
    transition: all 0.3s ease;
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

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* Badge Styles */
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
    transition: background-color 0.3s ease;
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
    animation: slideInDown 0.5s ease;
}

@keyframes slideInDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
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
    border-radius: 15px;
    padding: 2em;
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

.swal2-confirm, .swal2-cancel, .swal2-deny {
    border-radius: 8px !important;
    padding: 12px 24px !important;
    font-weight: 500 !important;
    box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08) !important;
    margin: 0 5px !important;
}

.swal2-confirm:hover, .swal2-cancel:hover, .swal2-deny:hover {
    transform: translateY(-1px);
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08) !important;
}

.swal2-icon {
    margin: 1em auto;
}

/* Toastr Custom Styles */
.toast-success {
    background-color: #28a745 !important;
}

.toast-info {
    background-color: #17a2b8 !important;
}

.toast-warning {
    background-color: #ffc107 !important;
}

.toast-error {
    background-color: #dc3545 !important;
}

#toast-container > div {
    border-radius: 8px !important;
    box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08) !important;
    opacity: 1 !important;
    padding: 15px 15px 15px 50px !important;
}

/* Toast Bootstrap */
.toast {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
}

.toast-header {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
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

    .swal2-confirm, .swal2-cancel, .swal2-deny {
        width: 100% !important;
        margin: 5px 0 !important;
    }

    .swal2-actions {
        flex-direction: column !important;
    }
}

/* Animation for new data */
@keyframes highlight {
    0% {
        background-color: #fff3cd;
    }
    100% {
        background-color: transparent;
    }
}

.highlight {
    animation: highlight 2s ease;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Card hover effect */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

/* Refresh button animation */
.btn-info i.fa-refresh {
    transition: transform 0.5s ease;
}

.btn-info:hover i.fa-refresh {
    transform: rotate(360deg);
}
</style>
@endsection
