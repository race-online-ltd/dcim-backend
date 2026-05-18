<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class MyController extends Controller
{
    public function myMethod(Request $request)
    {
        $guardName = config('auth.defaults.guard', 'web');

        $apiRouteNames = collect(Route::getRoutes())
            ->filter(fn($route) => $route->getName() && str_starts_with($route->uri(), 'api/'))
            ->map(fn($route) => $route->getName())
            ->unique()
            ->values();

        $created = [];
        $existing = [];
        $removedStale = [];
        $removedDuplicates = [];

        $stalePermissionIds = Permission::where('guard_name', $guardName)
            ->whereNotIn('name', $apiRouteNames)
            ->pluck('id')
            ->toArray();

        if (!empty($stalePermissionIds)) {
            DB::table('role_has_permissions')
                ->whereIn('permission_id', $stalePermissionIds)
                ->delete();

            DB::table('model_has_permissions')
                ->whereIn('permission_id', $stalePermissionIds)
                ->delete();

            Permission::whereIn('id', $stalePermissionIds)->delete();
            $removedStale = $stalePermissionIds;
        }

        $duplicateNames = Permission::where('guard_name', $guardName)
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicateNames as $duplicateName) {
            $duplicates = Permission::where('guard_name', $guardName)
                ->where('name', $duplicateName)
                ->orderBy('id')
                ->get();

            $keepPermission = $duplicates->first();
            $duplicateIds = $duplicates->pluck('id')->slice(1)->all();

            if (empty($duplicateIds)) {
                continue;
            }

            $duplicateAssignments = DB::table('role_has_permissions')
                ->whereIn('permission_id', $duplicateIds)
                ->get();

            foreach ($duplicateAssignments as $assignment) {
                DB::table('role_has_permissions')->updateOrInsert(
                    [
                        'role_id' => $assignment->role_id,
                        'permission_id' => $keepPermission->id,
                    ],
                    []
                );
            }

            $duplicateModelAssignments = DB::table('model_has_permissions')
                ->whereIn('permission_id', $duplicateIds)
                ->get();

            foreach ($duplicateModelAssignments as $assignment) {
                DB::table('model_has_permissions')->updateOrInsert(
                    [
                        'model_id' => $assignment->model_id,
                        'model_type' => $assignment->model_type,
                        'permission_id' => $keepPermission->id,
                    ],
                    []
                );
            }

            DB::table('role_has_permissions')
                ->whereIn('permission_id', $duplicateIds)
                ->delete();

            DB::table('model_has_permissions')
                ->whereIn('permission_id', $duplicateIds)
                ->delete();

            Permission::where('guard_name', $guardName)
                ->where('name', $duplicateName)
                ->whereIn('id', $duplicateIds)
                ->delete();

            $removedDuplicates[] = $duplicateName;
        }

        foreach ($apiRouteNames as $name) {
            $permission = Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guardName,
            ]);

            if ($permission->wasRecentlyCreated) {
                $created[] = $name;
            } else {
                $existing[] = $name;
            }
        }

        return response()->json([
            'message' => 'Route permissions synced and cleaned successfully.',
            'api_route_count' => $apiRouteNames->count(),
            'created_count' => count($created),
            'existing_count' => count($existing),
            'removed_stale_count' => 0,
            'removed_duplicate_count' => count($removedDuplicates),
            'created' => $created,
            'existing' => $existing,
            'removed_duplicates' => $removedDuplicates,
        ]);
    }
}
