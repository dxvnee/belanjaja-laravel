<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::where('user_id', Auth::id())->get()->toArray();

        return Inertia::render('ManageAddress', [
            'addresses' => $addresses,
        ]);
    }

    public function store(StoreAddressRequest $request)
    {
        Address::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('address.index')->with('success', 'Alamat berhasil ditambahkan!');
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        $address->update($request->validated());

        return redirect()->route('address.index')->with('success', 'Alamat berhasil diperbarui!');
    }

    public function destroy(Address $address)
    {
        Gate::authorize('delete', $address);

        $address->delete();

        return redirect()->route('address.index')->with('success', 'Alamat berhasil dihapus!');
    }
}
