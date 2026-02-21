<?php

namespace App\Http\Controllers\Karyawan;

use Carbon\Carbon;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddOrderRequest;
use Illuminate\Support\Facades\Session;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Harga;
use App\Models\DataBank;
use App\Jobs\DoneCustomerJob;
use App\Jobs\OrderCustomerJob;
use App\Notifications\{OrderMasuk,OrderSelesai};

class PelayananController extends Controller
{
    // Halaman list order masuk
    public function index()
    {
        $order = Transaksi::with('price')
            ->where('user_id', Auth::user()->id)
            ->orderBy('id', 'DESC')
            ->get();
        return view('karyawan.transaksi.order', compact('order'));
    }

    // Proses simpan order
    public function store(AddOrderRequest $request)
    {
        try {
            DB::beginTransaction();
            $order = new Transaksi();
            $order->invoice         = $request->invoice;
            $order->tgl_transaksi   = Carbon::now()->parse($order->tgl_transaksi)->format('d-m-Y');
            $order->status_payment  = $request->status_payment;
            $order->harga_id        = $request->harga_id;
            $order->customer_id     = $request->customer_id;
            $order->user_id         = Auth::user()->id;
            $order->customer        = namaCustomer($order->customer_id);
            $order->email_customer  = email_customer($order->customer_id);
            $order->hari            = $request->hari;
            $order->kg              = $request->kg;
            $order->harga           = $request->harga;
            $order->disc            = $request->disc;
            $hitung                 = $order->kg * $order->harga;
            if ($request->disc != NULL) {
                $disc                = ($hitung * $order->disc) / 100;
                $total               = $hitung - $disc;
                $order->harga_akhir  = $total;
            } else {
                $order->harga_akhir  = $hitung;
            }
            $order->jenis_pembayaran  = $request->jenis_pembayaran;
            $order->tgl               = Carbon::now()->day;
            $order->bulan             = Carbon::now()->month;
            $order->tahun             = Carbon::now()->year;
            $order->save();

            if ($order) {
                // Notification Telegram
                if (setNotificationTelegramIn(1) == 1) {
                    $order->notify(new OrderMasuk());
                }

                // Notification email
                if (setNotificationEmail(1) == 1) {
                    // Menyiapkan data Email
                    $bank = DataBank::get();
                    $jenisPakaian = Harga::where('id', $order->harga_id)->first();
                    $data = array(
                        'email'         => $order->email_customer,
                        'invoice'       => $order->invoice,
                        'customer'      => $order->customer,
                        'tgl_transaksi' => $order->tgl_transaksi,
                        'pakaian'       => $jenisPakaian->jenis,
                        'berat'         => $order->kg,
                        'harga'         => $order->harga,
                        'harga_disc'    => ($hitung * $order->disc) / 100,
                        'disc'          => $order->disc,
                        'total'         => $order->kg * $order->harga,
                        'harga_akhir'   => $order->harga_akhir,
                        'laundry_name'  => Auth::user()->nama_cabang,
                        'bank'          => $bank
                    );

                    // Kirim Email
                    dispatch(new OrderCustomerJob($data));
                }

                // **NOTIFIKASI WHATSAPP ORDER BARU**
                if (getTokenWhatsapp() != null) {
                    $waCustomer = $order->customers->no_telp;
                    $nameCustomer = $order->customers->name;
                    $jenisPakaian = Harga::where('id', $order->harga_id)->first();

                    // Hitung estimasi selesai
                    $tglSelesai = Carbon::parse($order->tgl_transaksi)->addDays($order->hari)->format('d-m-Y');

                    // Format harga
                    $hargaFormat = 'Rp ' . number_format($order->harga_akhir, 0, ',', '.');

                    // Status pembayaran
                    $statusPayment = $order->status_payment == 'Pending' ? '⏳ MENUNGGU PEMBAYARAN' : '✅ LUNAS';

                    // Pesan WhatsApp Order Baru
                    $message = "Halo Kak *{$nameCustomer}*,\n\n";
                    $message .= "✅ *ORDER BARU DITERIMA*\n";
                    $message .= "Terima kasih telah order laundry di *" . Auth::user()->nama_cabang . "*\n\n";
                    $message .= "📋 *DETAIL TRANSAKSI*\n";
                    $message .= "─────────────────\n";
                    $message .= "📝 Invoice : {$order->invoice}\n";
                    $message .= "👕 Layanan : {$jenisPakaian->jenis}\n";
                    $message .= "⚖️ Berat : {$order->kg} Kg\n";
                    $message .= "💰 Total : {$hargaFormat}\n";
                    $message .= "💳 Status Bayar : {$statusPayment}\n";
                    $message .= "📅 Estimasi Selesai : {$tglSelesai}\n";
                    $message .= "─────────────────\n\n";

                    if ($order->status_payment == 'Pending') {
                        $message .= "Silahkan lakukan pembayaran untuk memproses laundry Anda.\n\n";
                    }

                    $message .= "Kami akan proses laundry Anda. Terima kasih! 🙏";

                    notificationWhatsapp(
                        getTokenWhatsapp(),
                        $waCustomer,
                        $message
                    );
                }

                DB::commit();
                Session::flash('success', 'Order Berhasil Ditambah !');
                return redirect('pelayanan');
            }
        } catch (ErrorException $e) {
            DB::rollback();
            throw new ErrorException($e->getMessage());
        }
    }

    // Tambah Order
    public function addorders()
    {
        $customer = User::where('karyawan_id', Auth::user()->id)->get();
        $jenisPakaian = Harga::where('user_id', Auth::id())->where('status', '1')->get();

        $y = date('Y');
        $number = mt_rand(1000, 9999);
        // Nomor Form otomatis
        $newID = $number . Auth::user()->id . '' . $y;
        $tgl = date('d-m-Y');

        $cek_harga = Harga::where('user_id', Auth::user()->id)->where('status', 1)->first();
        $cek_customer = User::select('id', 'karyawan_id')->where('karyawan_id', Auth::id())->count();
        return view('karyawan.transaksi.addorder', compact('customer', 'newID', 'cek_harga', 'cek_customer', 'jenisPakaian'));
    }

    // Filter List Harga
    public function listharga(Request $request)
    {
        $list_harga = Harga::select('id', 'harga')
            ->where('user_id', Auth::user()->id)
            ->where('id', $request->id)
            ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Harga</label>
                    <select id="harga" class="form-control" name="harga" value="harga">
                    ';
        foreach ($list_harga as $studi) {
            $select .= '<option value="' . $studi->harga . '">' . 'Rp. ' . number_format($studi->harga, 0, ",", ".") . '</option>';
        }
        $select .= '
                    </select>
                    </div>';
        return $select;
    }

    // Filter List Jumlah Hari
    public function listhari(Request $request)
    {
        $list_jenis = Harga::select('id', 'hari')
            ->where('user_id', Auth::user()->id)
            ->where('id', $request->id)
            ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Pilih Hari</label>
                    <select id="hari" class="form-control" name="hari" value="hari">
                    ';
        foreach ($list_jenis as $hari) {
            $select .= '<option value="' . $hari->hari . '">' . $hari->hari . '</option>';
        }
        $select .= '
                    </select>
                    </div>';
        return $select;
    }

    // Update Status Laundry (MENGGABUNGKAN BAYAR DAN SELESAI DALAM SATU FUNGSI)
// Update Status Laundry (MENGGABUNGKAN BAYAR DAN SELESAI DALAM SATU FUNGSI)
public function updateStatusLaundry(Request $request)
{
    try {
        $transaksi = Transaksi::find($request->id);

        if (!$transaksi) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan'
                ]);
            }
            Session::flash('error', 'Transaksi tidak ditemukan');
            return redirect()->back();
        }

        // CEK APAKAH INI UNTUK PEMBAYARAN ATAU STATUS LAUNDRY
        if (isset($request->action) && $request->action == 'bayar') {
            // ACTION BAYAR - Bisa diklik kapan saja
            if ($transaksi->status_payment == 'Pending') {
                $transaksi->update([
                    'status_payment' => 'Success'
                ]);

                $message = 'Status pembayaran berhasil diubah menjadi Lunas!';
                Session::flash('success', $message);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }
            } elseif ($transaksi->status_payment == 'Success') {
                // Jika sudah lunas, tetap bisa diklik tapi kasih notifikasi
                $message = 'Status pembayaran sudah Lunas sebelumnya';

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }
                Session::flash('info', $message);
            }
        } else {
            // ACTION STATUS LAUNDRY (Selesai/Diambil)
            // TIDAK PERLU CEK STATUS PEMBAYARAN - Bisa selesai dulu baru bayar

            if ($transaksi->status_order == 'Process') {
                $transaksi->update([
                    'status_order' => 'Done'
                ]);

                // Tambah point +1
                $points = User::where('id', $transaksi->customer_id)->firstOrFail();
                $points->point =  $points->point + 1;
                $points->update();

                // Create Notifikasi
                $id         = $transaksi->id;
                $user_id    = $transaksi->customer_id;
                $title      = 'Pakaian Selesai';
                $body       = 'Pakaian Sudah Selesai dan Sudah Bisa Diambil :)';
                $kategori   = 'info';
                sendNotification($id, $user_id, $kategori, $title, $body);

                // Cek email notif (hanya jika pembayaran sudah lunas atau tetap kirim?)
                if (setNotificationEmail(1) == 1) {
                    // Menyiapkan data
                    $data = array(
                        'email'           => $transaksi->email_customer,
                        'invoice'         => $transaksi->invoice,
                        'customer'        => $transaksi->customer,
                        'nama_laundry'    => Auth::user()->nama_cabang,
                        'alamat_laundry'  => Auth::user()->alamat_cabang,
                        'status_payment'  => $transaksi->status_payment // Tambahkan info pembayaran
                    );

                    // Kirim Email
                    dispatch(new DoneCustomerJob($data));
                }

                // Cek status notif untuk telegram
                if (setNotificationTelegramFinish(1) == 1) {
                    $transaksi->notify(new OrderSelesai());
                }

                // **NOTIFIKASI WHATSAPP ORDER SELESAI**
                if (getTokenWhatsapp() != null) {
                    $waCustomer = $transaksi->customers->no_telp;
                    $nameCustomer = $transaksi->customers->name;
                    $jenisPakaian = Harga::where('id', $transaksi->harga_id)->first();

                    // Hitung estimasi selesai
                    $tglSelesai = Carbon::parse($transaksi->tgl_transaksi)->addDays($transaksi->hari)->format('d-m-Y');

                    // Format harga ke Rupiah
                    $hargaFormat = 'Rp ' . number_format($transaksi->harga_akhir, 0, ',', '.');

                    // Status pembayaran
                    $statusPayment = $transaksi->status_payment == 'Pending' ? '⏳ BELUM BAYAR (Bayar saat ambil)' : '✅ LUNAS';

                    // Pesan WhatsApp Order Selesai
                    $message = "Halo Kak *{$nameCustomer}*,\n\n";
                    $message .= "🎉 *LAUNDRY SELESAI*\n";
                    $message .= "Laundry kamu sudah selesai dan siap diambil!\n\n";
                    $message .= "📋 *DETAIL TRANSAKSI*\n";
                    $message .= "─────────────────\n";
                    $message .= "📝 Invoice : {$transaksi->invoice}\n";
                    $message .= "👕 Layanan : {$jenisPakaian->jenis}\n";
                    $message .= "⚖️ Berat : {$transaksi->kg} Kg\n";
                    $message .= "💰 Total : {$hargaFormat}\n";
                    $message .= "💳 Status Bayar : {$statusPayment}\n";
                    $message .= "📅 Estimasi Selesai : {$tglSelesai}\n";
                    $message .= "─────────────────\n\n";
                    $message .= "📍 *Ambil di:*\n";
                    $message .= "🏠 " . Auth::user()->nama_cabang . "\n";
                    $message .= "📌 " . Auth::user()->alamat_cabang . "\n\n";

                    if ($transaksi->status_payment == 'Pending') {
                        $message .= "⚠️ *Catatan:* Jangan lupa bayar saat ambil ya! Total yang harus dibayar: {$hargaFormat}\n\n";
                    }

                    $message .= "Terima kasih! 🙏";

                    notificationWhatsapp(
                        getTokenWhatsapp(),
                        $waCustomer,
                        $message
                    );
                }

                $message = 'Status Laundry Berhasil Diubah Menjadi Selesai!';
                Session::flash('success', $message);

            } elseif ($transaksi->status_order == 'Done') {
                $transaksi->update([
                    'status_order' => 'Delivery'
                ]);

                $message = 'Status Laundry Berhasil Diubah Menjadi Diambil!';
                Session::flash('success', $message);

            } else {
                $message = 'Status laundry tidak dapat diubah';

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ]);
                }
                Session::flash('error', $message);
                return redirect()->back();
            }
        }

        // Return response untuk AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message ?? 'Status berhasil diupdate'
            ]);
        }

        // Redirect balik ke halaman sebelumnya
        return redirect()->back();

    } catch (\Exception $e) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
        Session::flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        return redirect()->back();
    }
}
}
