<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\DcOwnerMapping;
use App\Models\User;

class CustomAuthController extends Controller
{
    // public function login(Request $request)
    // {
        
    //     $user = User::where('username', $request->username)->first();
    
    //     if (! $user || ! Hash::check($request->password, $user->password)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }
    
    //     return response()->json([
    //         'access_token' => $user->createToken('api-token')->plainTextToken,
    //         'data' => $user,
    //     ]);
    // }


    public function login(Request $request)
    {
        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Get data_center_ids
        $dataCenterIds = DcOwnerMapping::where('user_id', $user->id)
                            ->pluck('data_center_id')
                            ->toArray();

        // Attach to user object
        $user->data_center_ids = $dataCenterIds;

        return response()->json([
            'access_token' => $user->createToken('api-token')->plainTextToken,
            'data' => $user,
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
