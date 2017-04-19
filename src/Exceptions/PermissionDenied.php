<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Exceptions;

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
        $this->message = "You don't have a required permission '{$permission}'";
    }
}
