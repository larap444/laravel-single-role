<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Exceptions;

use Illuminate\Support\Facades\Lang;

/**
 * Class RoleDeniedException
 *
 * @package McMatters\SingleRole\Exceptions
 */
class RoleDeniedException extends AccessDenied
{
    /**
     * RoleDeniedException constructor.
     *
     * @param string $role
     */
    public function __construct(string $role)
    {
        parent::__construct(Lang::trans(
            'single-role::single-role.exceptions.role',
            ['role' => $role]
        ));
    }
}
