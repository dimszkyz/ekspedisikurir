<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    /**
     * Menampilkan semua alamat milik pengguna.
     */
    public function index()
    {
        $userId = Auth::id();
        $addresses = Address::where('user_id', $userId)->orderByDesc('isdefault')->get();
        return view('user.address', compact('addresses'));
    }

    /**
     * Menampilkan form untuk menambah alamat baru.
     */
    public function address_add()
    {
        return view('user.address-add');
    }

    /**
     * Menyimpan alamat baru ke database.
     */
    public function address_store(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validated = $request->validate([
            'name'      => 'required|max:100',
            'phone'     => 'required|numeric|digits_between:10,13',
            'locality'  => 'required|string|max:255',
            'address'   => 'required|string',
            'city'      => 'required|string|max:100',
            'state'     => 'required|string|max:100',
            'landmark'  => 'required|max:255', // WAJIB
            'zip'       => 'required|numeric|digits:5',
            'type'      => 'required|in:Rumah,Kantor,Lainnya', // WAJIB
            'isdefault' => 'nullable|boolean',
        ]);

        $validated['country'] = $request->input('country', 'Indonesia');

        // Cek apakah checkbox isdefault dicentang
        $validated['isdefault'] = $request->has('isdefault') ? 1 : 0;

        // Jika alamat ini default, nonaktifkan alamat default lama
        if ($validated['isdefault']) {
            Address::where('user_id', $user->id)->update(['isdefault' => false]);
        }

        // Simpan alamat baru
        $address = new Address($validated);
        $address->user_id = $user->id;
        $address->save();

        return redirect()->route('user.address.index')->with('success', 'Alamat berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit alamat.
     */
    public function address_edit($id)
    {
        // Gunakan findOrFail untuk keamanan dan otomatisasi error 404 jika ID tidak ditemukan
        $address = Address::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('user.address-edit', compact('address'));
    }

    /**
     * Memperbarui alamat yang ada di database.
     */
    public function address_update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20',
            'zip'       => 'required|string|max:10',
            'state'     => 'required|string|max:100',
            'city'      => 'required|string|max:100',
            'address'   => 'required|string|max:255',
            'locality'  => 'required|string|max:255',
            'landmark'  => 'required|string|max:255', // WAJIB
            'type'      => 'required|in:Rumah,Kantor,Lainnya', // WAJIB
            'isdefault' => 'nullable|boolean',
        ]);

        $address = Address::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Jika centang isdefault, nonaktifkan semua isdefault lain milik user
        if ($request->has('isdefault')) {
            Address::where('user_id', Auth::id())->where('id', '!=', $id)->update(['isdefault' => false]);
            $validated['isdefault'] = true;
        } else {
            $validated['isdefault'] = false;
        }

        $address->update($validated);

        return redirect()->route('user.address.index')->with('success', 'Alamat berhasil diupdate!');
    }

    /**
     * Menghapus alamat dari database.
     */
    public function address_delete($id)
    {
        // Cari alamat milik user yang sedang login untuk keamanan
        $address = Address::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        // Hapus object yang sudah ditemukan
        $address->delete(); 
        
        return redirect()->route('user.address.index')->with('success', 'Alamat berhasil dihapus!');
    }
}