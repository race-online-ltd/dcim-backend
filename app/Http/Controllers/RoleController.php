<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
        $this->middleware('permission:role-create', ['only' => ['create','store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function index(Request $request): View
//    {
//        $roles = Role::orderBy('id','DESC')->paginate(5);
//        return view('roles.index',compact('roles'))
//            ->with('i', ($request->input('page', 1) - 1) * 5);
//    }

    public function index(): JsonResponse
    {
        $roles = Role::orderBy('id', 'DESC')->paginate(5);
        return response()->json($roles);
    }

//    public function index()
//    {
////        return Role::paginate(5);
//        return Role::all();
//    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function create(): View
//    {
//        $permission = Permission::get();
//        return view('roles.create',compact('permission'));
//    }

    public function create()
    {
        $guardName = config('auth.defaults.guard', 'web');

        $apiRouteNames = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName() && str_starts_with($route->uri(), 'api/'))
            ->map(fn($route) => $route->getName())
            ->unique()
            ->values();

        $permissions = Permission::where('guard_name', $guardName)
            ->whereIn('name', $apiRouteNames)
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'permissions' => $permissions
        ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
//    public function store(Request $request): RedirectResponse
//    {
//        $this->validate($request, [
//            'name' => 'required|unique:roles,name',
//            'permission' => 'required',
//        ]);
//
//        $permissionsID = array_map(
//            function($value) { return (int)$value; },
//            $request->input('permission')
//        );
//
//        $role = Role::create(['name' => $request->input('name')]);
//        $role->syncPermissions($permissionsID);
//
//        return redirect()->route('roles.index')
//            ->with('success','Role created successfully');
//    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required|array',
        ]);

        $permissionIds = collect($validated['permission'])
            ->map('intval')
            ->filter()
            ->unique()
            ->toArray();

        $guardName = config('auth.defaults.guard', 'web');
        $validPermissionIds = Permission::whereIn('id', $permissionIds)
            ->where('guard_name', $guardName)
            ->pluck('id')
            ->toArray();

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guardName,
        ]);

        $role->syncPermissions($validPermissionIds);

        return response()->json([
            'message' => 'Role created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function show($id): View
//    {
//        $role = Role::find($id);
//        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
//            ->where("role_has_permissions.role_id",$id)
//            ->get();
//
//        return view('roles.show',compact('role','rolePermissions'));
//    }


    public function show($id)
    {
        $role = Role::findOrFail($id);
        $rolePermissions = \Spatie\Permission\Models\Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();

        return response()->json([
            'role' => $role,
            'permissions' => $rolePermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function edit($id): View
//    {
//        $role = Role::find($id);
//        $permission = Permission::get();
//        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
//            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
//            ->all();
//
//        return view('roles.edit',compact('role','permission','rolePermissions'));
//    }

    public function edit($id)
    {
        $guardName = config('auth.defaults.guard', 'web');

        $apiRouteNames = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName() && str_starts_with($route->uri(), 'api/'))
            ->map(fn($route) => $route->getName())
            ->unique()
            ->values();

        $role = \Spatie\Permission\Models\Role::findOrFail($id);
        $permission = Permission::where('guard_name', $guardName)
            ->whereIn('name', $apiRouteNames)
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();

        $rolePermissions = Permission::join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('role_has_permissions.role_id', $id)
            ->where('permissions.guard_name', $guardName)
            ->whereIn('permissions.name', $apiRouteNames)
            ->pluck('permissions.id')
            ->unique()
            ->toArray();

        return response()->json([
            'role' => $role,
            'all_permissions' => $permission,
            'role_permissions' => $rolePermissions
        ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function update(Request $request, $id): RedirectResponse
//    {
//        $this->validate($request, [
//            'name' => 'required',
//            'permission' => 'required',
//        ]);
//
//        $role = Role::find($id);
//        $role->name = $request->input('name');
//        $role->save();
//
//        $permissionsID = array_map(
//            function($value) { return (int)$value; },
//            $request->input('permission')
//        );
//
//        $role->syncPermissions($permissionsID);
//
//        return redirect()->route('roles.index')
//            ->with('success','Role updated successfully');
//    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permission' => 'required|array',
        ]);

        $guardName = config('auth.defaults.guard', 'web');
        $role = \Spatie\Permission\Models\Role::findOrFail($id);
        $role->name = $validated['name'];
        $role->save();

        $permissionIds = collect($validated['permission'])
            ->map('intval')
            ->filter()
            ->unique()
            ->toArray();

        $validPermissionIds = Permission::whereIn('id', $permissionIds)
            ->where('guard_name', $guardName)
            ->pluck('id')
            ->toArray();

        $role->syncPermissions($validPermissionIds);

        return response()->json([
            'message' => 'Role updated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function destroy($id): RedirectResponse
//    {
//        DB::table("roles")->where('id',$id)->delete();
//        return redirect()->route('roles.index')
//            ->with('success','Role deleted successfully');
//    }

    public function destroy($id)
    {
        $deleted = DB::table("roles")->where('id', $id)->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Role deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Role not found or already deleted'
            ], 404);
        }
    }



    public function showNew($id)
    {
        $role = Role::findOrFail($id);
        $permissions = $role->permissions;

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $permissions
        ]);
    }


    public function getEditData($id)
    {
        $role = Role::findOrFail($id);
        $allPermissions = Permission::all();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
            'all_permissions' => $allPermissions,
            'assigned_permission_ids' => $rolePermissionIds,
        ]);
    }

    public function updateNew(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'required|array',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->save();

        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Role updated successfully.']);
    }


}
