<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\InternetProtocolAddress;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index()
    {
        return response()->json(Audit::with('user')->paginate());
    }

    public function show(Audit $audit)
    {
        return response()->json($audit);
    }

    public function update(Request $request, Audit $audit)
    {
        $audit->update($request->all());
        $data = $audit->fresh();
        return response()->json($data);
    }

    public function delete(Audit $audit)
    {
        $audit->delete();
        return response()->json(['message' => 'delete successfull'], 204);
    }
}
