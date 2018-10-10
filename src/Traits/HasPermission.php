<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Models\Permission;
use McMatters\SingleRole\Models\Role;
use const false, null, true;
use function class_uses, explode, get_class, in_array, is_numeric, is_string;

/**
 * Class HasPermission
 *
 * @package McMatters\SingleRole\Traits
 */
trait HasPermission
{
    /**
     * @var array
     */
    protected static $cachedPermissions = [];

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
            null,
            null,
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
        $permissions = $this->getPermissions();

        return (bool) $permissions->first(function (Permission $item) use ($permission) {
            return is_numeric($permission)
                ? $item->getKey() === (int) $permission
                : $item->getAttribute('name') === $permission;
        });
    }

    /**
     * @param mixed $permissions
     * @param bool $all
     *
     * @return bool
     */
    public function hasPermissions($permissions, $all = false): bool
    {
        if (is_string($permissions)) {
            $permissions = explode(Config::get('single-role.delimiter'), $permissions);
        }

        foreach ((array) $permissions as $permission) {
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
     * @return Collection
     */
    public function getPermissions(): Collection
    {
        $class = get_class($this);
        $key = $this->getKey();

        if (isset(self::$cachedPermissions[$class][$key])) {
            return self::$cachedPermissions[$class][$key];
        }

        if (in_array(HasRole::class, class_uses($class), true)) {
            /** @var null|Role $role */
            $role = $this->getAttribute('role');
            $modelPermissions = $this->getAttribute('permissions');

            if (null !== $role) {
                $modelPermissions = $modelPermissions->merge(
                    $role->getAttribute('permissions')
                );
            }
        } else {
            $modelPermissions = $this->getAttribute('permissions');
        }

        self::$cachedPermissions[$class][$key] = $modelPermissions;

        return $modelPermissions;
    }

    /**
     * @param mixed $id
     * @param array $attributes
     * @param bool $touch
     *
     * @return self
     */
    public function attachPermissions(
        $id,
        array $attributes = [],
        bool $touch = true
    ): self {
        $this->permissions()->attach($id, $attributes, $touch);
        $this->updateCachedPermissions();

        return $this;
    }

    /**
     * @param mixed $ids
     * @param bool $touch
     *
     * @return self
     */
    public function detachPermissions($ids = null, bool $touch = true): self
    {
        $this->permissions()->detach($ids, $touch);
        $this->updateCachedPermissions();

        return $this;
    }

    /**
     * @param mixed $ids
     * @param bool $detaching
     *
     * @return $this
     */
    public function syncPermissions($ids, bool $detaching = true): self
    {
        $this->permissions()->sync($ids, $detaching);
        $this->updateCachedPermissions();

        return $this;
    }

    /**
     * @return void
     */
    protected function updateCachedPermissions(): self
    {
        self::$cachedPermissions[get_class($this)][$this->getKey()] = $this
            ->permissions()
            ->get();
    }
}
