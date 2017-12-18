<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Middleware;

use Closure;
use Illuminate\Http\Request;
use McMatters\SingleRole\Exceptions\PermissionDenied;
use const null;

/**
 * Class CheckPermission
 *
 * @package McMatters\SingleRole\Middleware
 */
class CheckPermission
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string $permission
     *
     * @return mixed
     * @throws PermissionDenied
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (null !== $user && $user->hasPermissions($permission)) {
            return $next($request);
        }

        throw new PermissionDenied($permission);
    }
}
