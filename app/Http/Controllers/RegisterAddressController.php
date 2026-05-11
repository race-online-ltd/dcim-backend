<?php

namespace App\Http\Controllers;

use App\Models\RegisterAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterAddressController extends Controller
{
    // GET /register-addresses
    public function index(): JsonResponse
    {
        $addresses = RegisterAddress::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Register address list fetched successfully.',
            'data'    => $addresses,
        ], 200);
    }

    // POST /register-addresses
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:register_addresses,name',
        ]);

        $address = RegisterAddress::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Register address created successfully.',
            'data'    => $address,
        ], 201);
    }

    // GET /register-addresses/{id}
    public function show(int $id): JsonResponse
    {
        $address = RegisterAddress::find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Register address not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Register address fetched successfully.',
            'data'    => $address,
        ], 200);
    }

    // PUT /register-addresses/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $address = RegisterAddress::find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Register address not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:register_addresses,name,' . $id,
        ]);

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Register address updated successfully.',
            'data'    => $address->fresh(),
        ], 200);
    }

    // DELETE /register-addresses/{id}
    public function destroy(int $id): JsonResponse
    {
        $address = RegisterAddress::find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Register address not found.',
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Register address deleted successfully.',
        ], 200);
    }
}