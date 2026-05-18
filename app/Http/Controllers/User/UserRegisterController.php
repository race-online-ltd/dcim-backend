<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;


class UserRegisterController extends Controller
{

    public function Register(Request $request)
    {

//        return 'here';
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'fullname' => 'required|string|max:255',
            'mobile' => 'required|string|max:100|unique:users,mobile',
            'email' => 'required|string|email|max:100|unique:users,email',
            'dept_id' => 'required|integer',
            'role_id' => 'required|integer',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'username.unique' => 'The username already exists.',
            'mobile.unique' => 'The mobile number is already registered.',
            'email.unique' => 'The email address is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isEmail = $request->has('is_email') ? $request->is_email : 0;
        $isSms = $request->has('is_sms') ? $request->is_sms : 0;

        $user = User::create([
            'user_type_id' => $request->user_type_id,
            'username' => $request->username,
            'fullname' => $request->fullname,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'dept_id' => $request->dept_id,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
            'status' => 1,
            'is_email' => $isEmail,
            'is_sms' => $isSms,
            'password_change' => 0,
        ]);

        try {
            $role = Role::findById((int) $request->role_id, config('auth.defaults.guard', 'web'));
            $user->syncRoles([$role->name]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid role selected'], 422);
        }

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|required|string|max:100|unique:users,username,'.$id,
            'fullname' => 'sometimes|required|string|max:255',
            'mobile' => 'sometimes|required|string|max:100|unique:users,mobile,'.$id,
            'email' => 'sometimes|required|string|email|max:100|unique:users,email,'.$id,
            'dept_id' => 'sometimes|required|integer',
            'role_id' => 'sometimes|required|integer',
            'user_type_id' => 'sometimes|required|integer',
            'status' => 'sometimes|required|boolean',
            'is_email' => 'sometimes|required|boolean',
            'is_sms' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->all());

        if ($request->filled('role_id')) {
            try {
                $role = Role::findById((int) $request->role_id, config('auth.defaults.guard', 'web'));
                $user->syncRoles([$role->name]);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Invalid role selected'], 422);
            }
        }

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }


    public function getUserMapping()
    {
        $users = User::select('id', 'username')
            ->get();
        return response()->json($users);
    }

}
