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
        $data = InternetProtocolAddress::with('user')->paginate();
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
            return response()->json(['message' => 'Failed to create IP address'], 400);
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
        try {
            return DB::transaction(function () use ($request, $internetProtocolAddress) {
                $internetProtocolAddress->update($request->validated());

                if (!$internetProtocolAddress->fresh()->audits()->where('event', 'updated')->exists()) {
                    throw new \Exception('Audit record missing');
                }

                return response()->json($internetProtocolAddress->fresh(), 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update IP address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InternetProtocolAddress $internetProtocolAddress)
    {
        $internetProtocolAddress->findOrFail(request()->id)->delete();
        return response()->json(['message' => 'Delete successful'], 200);
    }
}
