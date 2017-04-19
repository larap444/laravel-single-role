<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use McMatters\SingleRole\Models\Role;

/**
 * Class HasRole
 *
 * @package McMatters\SingleRole\Traits
 */
trait HasRole
{
    /**
     * HasRole constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->fillable[] = 'role_id';
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id', 'role');
    }

    /**
     * @param $role
     *
     * @return bool
     */
    public function hasRole($role): bool
    {
        $currentRole = $this->attributes['role_id'];

        if (!is_numeric($role) && is_string($role)) {
            $role = Role::where('name', '=', $role)->first();

            if (null === $role) {
                return false;
            }

            return $currentRole === $role->getKey();
        }

        if (is_array($role)) {
            $firstRole = array_first($role);

            if (is_numeric($firstRole)) {
                return in_array($currentRole, $role, true);
            }

            if ($firstRole instanceof Model) {
                return in_array($currentRole, array_pluck($role, 'id'), true);
            }

            return false;
        }

        if ($role instanceof Collection) {
            return $role->whereStrict('id', $currentRole)->isNotEmpty();
        }

        return $currentRole === $role;
    }

    /**
     * @param int $role
     *
     * @return $this
     */
    public function attachRole(int $role)
    {
        $this->update(['role_id' => $role]);

        return $this;
    }

    /**
     * @return $this
     */
    public function detachRole()
    {
        $this->update(['role_id' => null]);

        return $this;
    }
}
