<?php

namespace App\Http\Controllers\Hrm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    /**
     * @param array<int, string> $permissionSlugs
     */
    protected function authorizeHrm(Request $request, array $permissionSlugs = []): void
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        $adminRoleSlugs = ['owner', 'admin', 'hr'];
        $hasAdminRole = $user->roles()->whereIn('slug', $adminRoleSlugs)->exists();
        if ($hasAdminRole) {
            return;
        }

        if ($permissionSlugs === []) {
            return;
        }

        $hasDirectPermission = $user->permissions()->whereIn('slug', $permissionSlugs)->exists();
        $hasRolePermission = $user->roles()->whereHas('permissions', function ($q) use ($permissionSlugs) {
            $q->whereIn('slug', $permissionSlugs);
        })->exists();

        if (!$hasDirectPermission && !$hasRolePermission) {
            abort(403, 'You do not have permission to access this HRM area.');
        }
    }
}

