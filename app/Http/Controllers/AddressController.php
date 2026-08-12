<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index(){
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get()->toArray();

        return Inertia::render('ManageAddress', [
            'addresses' => $addresses
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'subdistrict' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'detail' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        Address::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'province' => $validated['province'],
            'city' => $validated['city'],
            'subdistrict' => $validated['subdistrict'],
            'postal_code' => $validated['postal_code'],
            'detail' => $validated['detail'],
            'phone' => $validated['phone'],
        ]);

        return redirect()->route('address.index')->with('success', 'Alamat berhasil ditambahkan!');
    }

    public function update(Request $request, Address $address)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'subdistrict' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'detail' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $address->update($validated);

        return redirect()->route('address.index')->with('success', 'Alamat berhasil diperbarui!');
    }

    public function destroy(Address $address)
    {
        $address->delete();

        return back()->with('success', 'Alamat berhasil dihapus!');
    }
}
