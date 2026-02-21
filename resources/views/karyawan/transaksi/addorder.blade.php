@extends('layouts.backend')
@section('title','Tambah Data Order')
@section('content')
    @if (@$cek_harga->user_id == !null || @$cek_harga->user_id == Auth::user()->id)

    @if($message = Session::get('error'))
      <div class="alert alert-danger alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>{{ $message }}</strong>
      </div>
    @endif

    @if($message = Session::get('success'))
      <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>{{ $message }}</strong>
      </div>
    @endif

    <!-- Modal Loading -->
    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <h5 class="mt-4 text-primary">Sedang Menyimpan Data...</h5>
                    <p class="text-muted mb-0">Mohon tunggu sebentar</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Loading Submit -->
    <div class="modal fade" id="submitLoadingModal" tabindex="-1" role="dialog" aria-labelledby="submitLoadingModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="spinner-grow text-success" style="width: 4rem; height: 4rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <h5 class="mt-4 text-success">Memproses Transaksi...</h5>
                    <p class="text-muted mb-0">Mohon tunggu, data sedang diproses</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlay Loading -->
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9998;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
            <div class="spinner-border text-light" style="width: 5rem; height: 5rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <h4 class="text-white mt-3">Sedang Menyimpan Data...</h4>
        </div>
    </div>

    <div class="card card-outline-info">
      <div class="card-header">
          <h4 class="card-title">Form Tambah Data Order
              <a href="{{url('customers-create')}}" class="btn btn-danger btn-sm">
                  <i class="fa fa-plus"></i> Customer Baru
              </a>
          </h4>
      </div>
      <div class="card-body">
        {{-- Cek Apakah Customer ada --}}
        @if ($cek_customer != 0)
          <form action="{{route('pelayanan.store')}}" method="POST" id="orderForm">
            @csrf
            <div class="form-body">
              <div class="row p-t-20">
                  <div class="col-md-3">
                      <div class="form-group has-success">
                          <label class="control-label">Nama Customer <span class="text-danger">*</span></label>
                          <select name="customer_id" id="customer_id" class="form-control select2 @error('customer_id') is-invalid @enderror" required>
                              <option value="">-- Pilih Customer --</option>
                              @foreach ($customer as $customers)
                                  <option value="{{$customers->id}}" {{old('customer_id') == $customers->id ? 'selected' : ''}} >{{$customers->name}} - {{$customers->no_telp}}</option>
                              @endforeach
                          </select>
                          @error('customer_id')
                            <span class="invalid-feedback text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                          @enderror
                      </div>
                  </div>

                  <div class="col-md-3">
                      <div class="form-group has-success">
                          <label class="control-label">No Transaksi</label>
                          <input type="text" name="invoice" value="{{$newID}}" class="form-control @error('invoice') is-invalid @enderror" readonly>
                          @error('invoice')
                            <span class="invalid-feedback text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                          @enderror
                      </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group has-success">
                        <label class="control-label">Berat Pakaian (Kg) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" min="0.1" class="form-control form-control-danger @error('kg') is-invalid @enderror" value="{{old('kg')}}" name="kg" placeholder="Contoh: 2.5" autocomplete="off" required>
                            <div class="input-group-append">
                                <span class="input-group-text">Kg</span>
                            </div>
                        </div>
                        @error('kg')
                          <span class="invalid-feedback text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group has-success">
                        <label class="control-label">Status Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-control custom-select @error('status_payment') is-invalid @enderror" name="status_payment" required>
                            <option value="">-- Pilih Status Payment --</option>
                            <option value="Pending" {{old('status_payment') == 'Pending' ? 'selected' : ''}} >Belum Dibayar</option>
                            <option value="Success" {{old('status_payment') == 'Success' ? 'selected' : ''}}>Sudah Dibayar</option>
                        </select>
                        @error('status_payment')
                          <span class="invalid-feedback text-danger" role="alert">
                              <strong>{{ $message }}</strong>
                          </span>
                        @enderror
                    </div>
                  </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group has-success">
                      <label class="control-label">Jenis Pembayaran <span class="text-danger">*</span></label>
                      <select class="form-control custom-select @error('jenis_pembayaran') is-invalid @enderror" name="jenis_pembayaran" required>
                        <option value="">-- Pilih Jenis Pembayaran --</option>
                        <option value="Tunai" {{old('jenis_pembayaran') == 'Tunai' ? 'selected' : ''}} >Tunai</option>
                        <option value="Transfer" {{old('jenis_pembayaran') == 'Transfer' ? 'selected' : ''}}>Transfer</option>
                      </select>
                      @error('jenis_pembayaran')
                        <span class="invalid-feedback text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="form-group has-success">
                      <label class="control-label">Pilih Pakaian <span class="text-danger">*</span></label>
                      <select id="id" name="harga_id" class="form-control select2 @error('harga_id') is-invalid @enderror" required>
                          <option value="">-- Jenis Pakaian --</option>
                          @foreach($jenisPakaian as $jenis)
                            <option value="{{$jenis->id}}" {{old('harga_id') == $jenis->id ? 'selected' : '' }} >{{$jenis->jenis}}</option>
                          @endforeach
                      </select>
                      @error('harga_id')
                        <span class="invalid-feedback text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                  </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">Estimasi Hari</label>
                        <div id="select-hari" class="form-control bg-light" style="min-height: 38px; padding: 6px 12px;">
                            <span class="text-muted">Pilih jenis pakaian</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="control-label">Harga/Kg</label>
                        <div id="select-harga" class="form-control bg-light" style="min-height: 38px; padding: 6px 12px;">
                            <span class="text-muted">Pilih jenis pakaian</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group has-success">
                      <label class="control-label">Disc (%)</label>
                      <input type="number" name="disc" value="{{old('disc')}}" placeholder="Contoh: 10" min="0" max="100" class="form-control @error('disc') is-invalid @enderror">
                      @error('disc')
                        <span class="invalid-feedback text-danger" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                      @enderror
                      <small class="form-text text-muted">Kosongkan jika tidak ada diskon</small>
                  </div>
                </div>
              </div>

              <!-- Informasi Total Sementara -->
              <div class="row mt-3" id="infoTotal" style="display: none;">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Harga/Kg:</strong> <span id="displayHarga">Rp 0</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Berat:</strong> <span id="displayBerat">0 Kg</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Diskon:</strong> <span id="displayDiskon">0%</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Total:</strong> <span id="displayTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
              </div>

                <input type="hidden" name="tgl">
                <!--/row-->
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary mr-1 mb-1" id="btnSubmit">
                  <i class="fa fa-save"></i> Tambah
              </button>
              <button type="reset" class="btn btn-outline-warning mr-1 mb-1" id="btnReset">
                  <i class="fa fa-refresh"></i> Reset
              </button>
              <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mr-1 mb-1">
                  <i class="fa fa-arrow-left"></i> Kembali
              </a>
            </div>
          </form>
        @else
          <div class="col text-center">
            <img src="{{asset('backend/images/pages/empty.svg')}}" style="height:300px; width:auto; margin-top:10px">
            <h2 class="text-danger mt-3">
              Data Customer Masih Kosong !
            </h2>
            <a href="{{url('customers-create')}}" class="btn btn-primary mt-3">
                <i class="fa fa-plus"></i> Tambah Customer Baru
            </a>
          </div>
        @endif
      </div>
    </div>
    @else
      <div class="card">
        <div class="col text-center">
          <img src="{{asset('backend/images/pages/empty.svg')}}" style="height:500px; width:100%; margin-top:10px">
          <h2 class="mt-1">Data Harga Kosong / Tidak Aktif !</h2>
          <h4 class="text-muted">Mohon hubungi Administrator :)</h4>
        </div>
      </div>
    @endif
@endsection

@section('scripts')
<!-- Loading IO CSS -->
<style>
/* Loading Spinner */
.spinner-border, .spinner-grow {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    vertical-align: text-bottom;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

.spinner-grow {
    background-color: currentColor;
    opacity: 0;
    animation: spinner-grow .75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

@keyframes spinner-grow {
    0% { transform: scale(0); opacity: 0; }
    50% { opacity: 1; transform: none; }
}

/* Modal Loading */
.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-body {
    padding: 2rem;
}

/* Loading Overlay */
#loadingOverlay {
    transition: all 0.3s ease;
}

#loadingOverlay .spinner-border {
    width: 5rem;
    height: 5rem;
    border-width: 0.3rem;
}

/* Button Loading State */
.btn-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.8;
}

.btn-loading:after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.6s linear infinite;
}

/* Pulse Animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 1s infinite;
}

/* Fade In Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease;
}
</style>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        width: '100%',
        placeholder: '-- Pilih --',
        allowClear: true
    });

    // Cegah submit double dengan loading
    $('#orderForm').on('submit', function(e) {
        var form = $(this);
        var btnSubmit = $('#btnSubmit');

        // Validasi form
        if (form[0].checkValidity() === false) {
            e.preventDefault();
            e.stopPropagation();
            form.addClass('was-validated');

            // Tampilkan pesan error
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Harap lengkapi semua field yang wajib diisi!',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }

        // Cek apakah sudah dalam proses submit
        if (form.data('submitting')) {
            e.preventDefault();
            return false;
        }

        // Tandai bahwa form sedang di-submit
        form.data('submitting', true);

        // Tampilkan loading
        $('#submitLoadingModal').modal('show');

        // Disable tombol submit
        btnSubmit.prop('disabled', true)
                  .html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...')
                  .addClass('btn-loading');

        // Submit form setelah loading ditampilkan
        setTimeout(function() {
            form.off('submit').submit();
        }, 500);

        // Prevent double submit
        e.preventDefault();
        return false;
    });

    // Loading untuk tombol reset
    $('#btnReset').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true)
           .html('<i class="fa fa-spinner fa-spin"></i> Mereset...');

        setTimeout(function() {
            btn.prop('disabled', false)
               .html('<i class="fa fa-refresh"></i> Reset');
        }, 1000);
    });

    // Loading untuk link customer baru
    $('a[href="{{url('customers-create')}}"]').on('click', function(e) {
        e.preventDefault();
        var link = $(this);
        var href = link.attr('href');

        Swal.fire({
            title: 'Buat Customer Baru?',
            text: "Anda akan dialihkan ke halaman tambah customer",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#loadingModal').modal('show');
                setTimeout(function() {
                    window.location.href = href;
                }, 1000);
            }
        });
    });

    // Fungsi untuk memuat informasi harga dan hari
    function loadPriceInfo(id) {
        if (!id) {
            $('#select-hari').html('<span class="text-muted">Pilih jenis pakaian</span>');
            $('#select-harga').html('<span class="text-muted">Pilih jenis pakaian</span>');
            $('#infoTotal').hide();
            return;
        }

        // Tampilkan loading di select-hari dan select-harga
        $('#select-hari').html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
        $('#select-harga').html('<i class="fa fa-spinner fa-spin"></i> Memuat...');

        // Load estimasi hari
        $.get('{{ Url("listhari") }}', {
            '_token': $('meta[name=csrf-token]').attr('content'),
            'id': id
        }, function(resp) {
            $('#select-hari').html(resp);
        }).fail(function() {
            $('#select-hari').html('<span class="text-danger">Gagal memuat data</span>');
        });

        // Load harga
        $.get('{{ Url("listharga") }}', {
            '_token': $('meta[name=csrf-token]').attr('content'),
            'id': id
        }, function(resp) {
            $('#select-harga').html(resp);
            updateTotalPreview();
        }).fail(function() {
            $('#select-harga').html('<span class="text-danger">Gagal memuat data</span>');
        });
    }

    // Event change untuk select jenis pakaian
    $(document).on('change', '#id', function (e) {
        var id = $(this).val();
        loadPriceInfo(id);
    });

    // Hitung total preview saat input berubah
    $(document).on('keyup change', 'input[name="kg"], input[name="disc"], #id', function() {
        updateTotalPreview();
    });

    function updateTotalPreview() {
        var kg = parseFloat($('input[name="kg"]').val()) || 0;
        var disc = parseFloat($('input[name="disc"]').val()) || 0;
        var hargaText = $('#select-harga').text();
        var harga = 0;

        // Extract harga dari text
        var match = hargaText.match(/Rp\s*([\d,]+)/);
        if (match) {
            harga = parseInt(match[1].replace(/,/g, '')) || 0;
        }

        if (kg > 0 && harga > 0) {
            var subtotal = kg * harga;
            var diskonNominal = (subtotal * disc) / 100;
            var total = subtotal - diskonNominal;

            $('#displayHarga').text('Rp ' + formatRupiah(harga));
            $('#displayBerat').text(kg + ' Kg');
            $('#displayDiskon').text(disc + '%');
            $('#displayTotal').text('Rp ' + formatRupiah(total));
            $('#infoTotal').show();
        } else {
            $('#infoTotal').hide();
        }
    }

    function formatRupiah(angka) {
        if (angka === 0) return '0';
        var numberString = angka.toString().replace(/[^,\d]/g, ''),
            split = numberString.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    // Load data jika ada old value
    @if(old('harga_id'))
        loadPriceInfo('{{ old("harga_id") }}');
        setTimeout(function() {
            updateTotalPreview();
        }, 500);
    @endif

    // Validasi input berat (hanya angka dan titik)
    $('input[name="kg"]').on('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
        // Cegah multiple dots
        if ((this.value.match(/\./g) || []).length > 1) {
            this.value = this.value.replace(/\.+$/, '');
        }
    });

    // Validasi diskon (0-100)
    $('input[name="disc"]').on('input', function() {
        var val = parseInt(this.value);
        if (val > 100) this.value = 100;
        if (val < 0) this.value = 0;
    });

    // Konfirmasi sebelum meninggalkan halaman jika ada perubahan
    var formChanged = false;
    $('#orderForm input, #orderForm select').on('change', function() {
        formChanged = true;
    });

    $('#orderForm select').on('select2:select', function() {
        formChanged = true;
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    $('#orderForm').on('submit', function() {
        formChanged = false;
    });

    // Toast notification
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: true
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            html: '<ul style="text-align: left;">' +
                @foreach($errors->all() as $error)
                    '<li>{{ $error }}</li>' +
                @endforeach
                '</ul>',
            confirmButtonColor: '#dc3545'
        });
    @endif
});

// Fungsi global untuk loading
function showLoading() {
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

// Handle saat halaman dimuat
$(window).on('load', function() {
    // Sembunyikan loading jika ada
    hideLoading();
});

// Handle saat user klik back button
window.onpageshow = function(event) {
    if (event.persisted) {
        hideLoading();
        $('#btnSubmit').prop('disabled', false)
                      .html('<i class="fa fa-save"></i> Tambah')
                      .removeClass('btn-loading');
    }
};
</script>
@endsection
