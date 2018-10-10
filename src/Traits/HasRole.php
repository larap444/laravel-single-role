<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\{Builder, Model, Relations\BelongsTo};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Models\Role;
use const false, null, true;
use function array_merge, explode, is_array, is_numeric, is_string, strpos;

/**
 * Class HasRole
 *
 * @package McMatters\SingleRole\Traits
 */
trait HasRole
{
    /**
     * @var array
     */
    protected static $cachedRoles = [];

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id', 'role');
    }

    /**
     * @param Builder $builder
     * @param mixed $role
     *
     * @return Builder
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function scopeRole(Builder $builder, $role): Builder
    {
        return $builder->whereIn('role_id', $this->parseRoles($role));
    }

    /**
     * @param mixed $role
     *
     * @return bool
     */
    public function hasRole($role): bool
    {
        $currentRole = $this->attributes['role_id'];
        $delimiter = Config::get('single-role.delimiter');

        if (!is_numeric($role) && is_string($role)) {
            if (isset(self::$cachedRoles[$role])) {
                return self::$cachedRoles[$role]->getKey() === $currentRole;
            }

            if (strpos($role, $delimiter) !== false) {
                $role = explode($delimiter, $role);
            } else {
                /** @var Role $roleModel */
                $roleModel = Role::query()->where('name', $role)->first();

                if (null === $roleModel) {
                    return false;
                }

                self::$cachedRoles[$role] = $roleModel;

                return $currentRole === $roleModel->getKey();
            }
        }

        if (is_array($role)) {
            foreach ($role as $item) {
                if (is_numeric($item) && ((int) $item) === $currentRole) {
                    return true;
                }

                if (is_string($item)) {
                    if (isset(self::$cachedRoles[$item])) {
                        $item = self::$cachedRoles[$item];
                    } else {
                        $item = self::$cachedRoles[$item] = Role::query()
                            ->where('name', $item)
                            ->first();
                    }
                }

                if ($item instanceof Model && $item->getKey() === $currentRole) {
                    return true;
                }
            }

            return false;
        }

        if ($role instanceof Collection) {
            return $role->contains(function (Role $role) use ($currentRole) {
                return $role->getKey() === $currentRole;
            });
        }

        return $currentRole === $role;
    }

    /**
     * @param mixed $role
     *
     * @return self
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function attachRole($role): self
    {
        $this->forceFill(['role_id' => $this->parseRole($role)])->save();

        return $this;
    }

    /**
     * @return self
     */
    public function detachRole(): self
    {
        $this->forceFill(['role_id' => null])->save();

        return $this;
    }

    /**
     * @param mixed $role
     *
     * @return int|null
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function parseRole($role): ?int
    {
        if (null === $role) {
            return null;
        }

        if (is_string($role) && !is_numeric($role)) {
            if (isset(self::$cachedRoles[$role])) {
                return self::$cachedRoles[$role]->getKey();
            }

            self::$cachedRoles[$role] = Role::query()
                ->where('name', $role)
                ->firstOrFail();

            return self::$cachedRoles[$role]->getKey();
        }

        if ($role instanceof Model) {
            return $role->getKey();
        }

        return null;
    }

    /**
     * @param mixed $roles
     *
     * @return array
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function parseRoles($roles): array
    {
        if (!is_array($roles)) {
            return [$this->parseRole($roles)];
        }

        $roleIds = [];
        $roleNames = [];

        foreach ($roles as $role) {
            if (is_numeric($role)) {
                $roleIds[] = (int) $role;
            } elseif (is_string($role)) {
                $roleNames[] = $role;
            }
        }

        if (!empty($roleNames)) {
            $rolesCollection = Role::query()->whereIn('name', $roleNames)->get();

            /** @var Role $role */
            foreach ($rolesCollection as $role) {
                // Add roles to static cache.
                self::$cachedRoles[$role->getAttribute('name')] = $role;
            }

            $roleIds = array_merge($roleIds, $rolesCollection->modelKeys());
        }

        return $roleIds;
    }
}
