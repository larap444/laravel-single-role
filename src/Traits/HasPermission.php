<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Models\Permission;
use McMatters\SingleRole\Models\Role;

use function explode, get_class, is_numeric, is_string;

use const false, null, true;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
            __FUNCTION__
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

        return (bool) $permissions->first(static function ($item) use ($permission) {
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
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(): Collection
    {
        $class = get_class($this);
        $key = $this->getKey();

        if (!isset(self::$cachedPermissions[$class][$key])) {
            $this->setCachedPermissions($class, $key, $this->getAllPermissions());
        }

        return self::$cachedPermissions[$class][$key];
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
     * @return self
     */
    public function syncPermissions($ids, bool $detaching = true): self
    {
        $this->permissions()->sync($ids, $detaching);
        $this->updateCachedPermissions();

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function getAllPermissions(): Collection
    {
        if ($this instanceof Role) {
            return $this->getAttribute('permissions');
        }

        /** @var \McMatters\SingleRole\Models\Role|null $role */
        $role = $this->getAttribute('role');
        $modelPermissions = $this->getAttribute('permissions');

        if (null !== $role) {
            return $modelPermissions->merge(
                $role->getAttribute('permissions')
            );
        }

        return new Collection();
    }

    /**
     * @param string $class
     * @param int|string $key
     * @param \Illuminate\Support\Collection $permissions
     *
     * @return void
     */
    protected function setCachedPermissions(
        string $class,
        $key,
        Collection $permissions
    ): void {
        self::$cachedPermissions[$class][$key] = $permissions;
    }

    /**
     * @return void
     */
    protected function updateCachedPermissions(): void
    {
        $class = get_class($this);
        $key = $this->getKey();

        unset(self::$cachedPermissions[$class][$key]);

        $this->setCachedPermissions($class, $key, $this->getAllPermissions());
    }
}
