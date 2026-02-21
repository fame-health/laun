<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\AddCustomerRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // ===============================
    // INDEX
    // ===============================
    public function index()
    {
        $customer = User::where('karyawan_id', Auth::id())
            ->where('auth', 'Customer')
            ->orderBy('id', 'DESC')
            ->get();

        return view('karyawan.customer.index', compact('customer'));
    }

    // ===============================
    // DETAIL
    // ===============================
    public function detail($id)
    {
        $customer = User::with('transaksiCustomer')
            ->where('karyawan_id', Auth::id())
            ->where('id', $id)
            ->first();

        return view('karyawan.customer.detail', compact('customer'));
    }

    // ===============================
    // CREATE
    // ===============================
    public function create()
    {
        return view('karyawan.customer.create');
    }

    // ===============================
    // STORE (EMAIL DUMMY OTOMATIS)
    // ===============================
    public function store(AddCustomerRequest $request)
    {
        try {
            DB::beginTransaction();

            // Format nomor HP jadi 62
            $phone_number = preg_replace('/^0/', '62', $request->no_telp);

            // EMAIL DUMMY (100% unik)
            $dummyEmail = Str::uuid() . '@customer.local';

            // Password default (boleh diganti)
            $password = Hash::make('12345678');

            $addCustomer = User::create([
                'karyawan_id' => Auth::id(),
                'name'        => $request->name,
                'email'       => $dummyEmail, // <-- tidak null lagi
                'auth'        => 'Customer',
                'status'      => 'Active',
                'no_telp'     => $phone_number,
                'alamat'      => $request->alamat,
                'password'    => $password
            ]);

            $addCustomer->assignRole('Customer');

            DB::commit();

            Session::flash('success', 'Customer Berhasil Ditambah !');
            return redirect('customers');

        } catch (\Exception $e) {
            DB::rollback();
            throw new ErrorException($e->getMessage());
        }
    }
}
