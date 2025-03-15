<?php

namespace App\Http\Controllers;

use App\Models\InternetProtocolAddress;
use App\Http\Requests\StoreInternetProtocolAddressRequest;
use App\Http\Requests\UpdateInternetProtocolAdressRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternetProtocolAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = InternetProtocolAddress::with('user')->get();
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInternetProtocolAddressRequest $request)
    {

        $user_id = Auth::guard('api')->id();

        DB::beginTransaction();

        $data = InternetProtocolAddress::create([
            'user_id' => $user_id,
            'ip_address' => $request->validated('ip_address'),
            'label' => $request->validated('label'),
            'comment' => $request->validated('comment'),
        ]);

        if ($data->audits()->count() === 0) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create IP address'], 500);
        }

        DB::commit();

        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(InternetProtocolAddress $internetProtocolAddress)
    {
        return response()->json($internetProtocolAddress, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInternetProtocolAdressRequest $request, InternetProtocolAddress $internetProtocolAddress)
    {
        DB::transaction(function () use ($request, $internetProtocolAddress) {
            $internetProtocolAddress->update($request->validated());
            $data = $internetProtocolAddress->fresh();

            if (!$data->audits()->where('event', 'updated')->latest()->first()) {
                throw new \Exception('Audit failed');
            }

            return response()->json($data, 200);
        });

        return response()->json(['message' => 'Failed to update IP address'], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InternetProtocolAddress $internetProtocolAddress)
    {
        $internetProtocolAddress->findOrFail(request()->id)->delete();
        return response()->json(['message' => 'delete successfull'], 200);
    }
}
