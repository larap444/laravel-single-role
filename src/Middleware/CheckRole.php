<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Middleware;

use Closure;
use Illuminate\Http\Request;
use McMatters\SingleRole\Exceptions\RoleDeniedException;

/**
 * Class CheckRole
 *
 * @package McMatters\SingleRole\Middleware
 */
class CheckRole
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string $role
     *
     * @return mixed
     * @throws RoleDeniedException
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if ($user->check() && $user->hasRole($role)) {
            return $next($request);
        }

        throw new RoleDeniedException($role);
    }
}
