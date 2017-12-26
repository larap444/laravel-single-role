<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Exceptions;

use Illuminate\Support\Facades\Lang;

/**
 * Class PermissionDenied
 *
 * @package McMatters\SingleRole\Exceptions
 */
class PermissionDenied extends AccessDenied
{
    /**
     * PermissionDenied constructor.
     *
     * @param string $permission
     */
    public function __construct(string $permission)
    {
        parent::__construct(Lang::trans(
            'single-role::single-role.exceptions.permission',
            ['permission' => $permission]
        ));
    }
}
