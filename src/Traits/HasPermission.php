<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use McMatters\SingleRole\Models\Permission;
use McMatters\SingleRole\Models\Role;

/**
 * Class HasPermission
 *
 * @package McMatters\SingleRole\Traits
 */
trait HasPermission
{
    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            null,
            null,
            'permission_id',
            'permissions'
        );
    }

    /**
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        $currentPermissions = null;

        if ($this instanceof Role) {
            $currentPermissions = $this->getAttribute('permissions');
        } else {
            /** @var null|Role $role */
            $role = $this->getAttribute('role');
            $currentPermissions = $this->getAttribute('permissions');

            if (null !== $role) {
                $currentPermissions = $currentPermissions->merge(
                    $role->getAttribute('permissions')
                );
            }
        }

        return (bool) $currentPermissions->first(function ($item) use ($permission) {
            return is_numeric($permission)
                ? $item->getKey() === (int) $permission
                : $item->getAttribute('name') === $permission;
        });
    }

    /**
     * @param $permissions
     * @param bool $all
     *
     * @return bool
     */
    public function hasPermissions($permissions, $all = false): bool
    {
        if (is_string($permissions)) {
            $permissions = explode('|', $permissions);
        }

        foreach ($permissions as $permission) {
            $hasPermission = $this->hasPermission($permission);

            if ($hasPermission && !$all) {
                return true;
            }

            if (!$hasPermission && $all) {
                return false;
            }
        }

        return $all;
    }

    /**
     * @param mixed $id
     * @param array $attributes
     * @param bool $touch
     *
     * @return $this
     */
    public function attachPermissions(
        $id,
        array $attributes = [],
        bool $touch = true
    ) {
        $this->permissions()->attach($id, $attributes, $touch);

        return $this;
    }

    /**
     * @param null $ids
     * @param bool $touch
     *
     * @return $this
     */
    public function detachPermissions($ids = null, bool $touch = true)
    {
        $this->permissions()->detach($ids, $touch);

        return $this;
    }

    /**
     * @param Collection|array $ids
     * @param bool $detaching
     *
     * @return $this
     */
    public function syncPermissions($ids, bool $detaching = true)
    {
        $this->permissions()->sync($ids, $detaching);

        return $this;
    }
}
