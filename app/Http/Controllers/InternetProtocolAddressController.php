<?php

namespace App\Http\Controllers;

use App\Models\InternetProtocolAddress;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInternetProtocolAddressRequest;
use Illuminate\Support\Facades\Auth;

class InternetProtocolAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInternetProtocolAddressRequest $request)
    {

        $user_id = auth('sanctum')->id();

        $data = InternetProtocolAddress::create([
            'user_id' => $user_id,
            'ip_address' => $request->validated('ip_address'),
            'label' => $request->validated('label'),
            'comment' => $request->validated('comment'),
        ]);

        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InternetProtocolAddress $internetProtocolAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InternetProtocolAddress $internetProtocolAddress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InternetProtocolAddress $internetProtocolAddress)
    {
        //
    }
}
