<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Models\Role;

use function array_merge, explode, is_array, is_numeric, is_string, strpos;

use const false, null, true;

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
     * @return array
     */
    public function getFillable(): array
    {
        return array_merge(parent::getFillable(), ['role_id']);
    }

    /**
     * @return array
     */
    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), ['role_id' => 'int']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'role_id',
            $this->primaryKey,
            __FUNCTION__
        );
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed $role
     *
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function scopeRole(Builder $builder, $role): Builder
    {
        return $builder->whereIn("{$this->table}.role_id", $this->parseRoles($role));
    }

    /**
     * @param mixed $role
     *
     * @return bool
     */
    public function hasRole($role): bool
    {
        $currentRole = $this->getAttribute('role_id');
        $delimiter = Config::get('single-role.delimiter');

        if (!is_numeric($role) && is_string($role)) {
            if (isset(self::$cachedRoles[$role])) {
                return self::$cachedRoles[$role]->getKey() === $currentRole;
            }

            if (strpos($role, $delimiter) !== false) {
                $role = explode($delimiter, $role);
            } else {
                /** @var \McMatters\SingleRole\Models\Role $roleModel */
                $roleModel = Role::query()->where('name', $role)->first();

                if (null === $role) {
                    return false;
                }

                self::$cachedRoles[$role] = $roleModel;

                return $currentRole === $roleModel->getKey();
            }
        }

        if (is_array($role)) {
            foreach ($role as $item) {
                if (is_numeric($item) && (int) $item === $currentRole) {
                    return true;
                }

                if (is_string($item)) {
                    $item = self::$cachedRoles[$item] ?? Role::query()
                            ->where('name', $item)
                            ->first();
                }

                if ($item instanceof Model && $item->getKey() === $currentRole) {
                    return true;
                }
            }

            return false;
        }

        if ($role instanceof Collection) {
            return $role->contains(static function (Role $role) use ($currentRole) {
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
        $this->update(['role_id' => $this->parseRole($role)]);

        return $this;
    }

    /**
     * @return self
     */
    public function detachRole(): self
    {
        $this->update(['role_id' => null]);

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

            $role = self::$cachedRoles[$role]->getKey();
        } elseif ($role instanceof Model) {
            $role = $role->getKey();
        }

        return (int) $role;
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
            $roleIds = array_merge(
                $roleIds,
                Role::query()->whereIn('name', $roleNames)->pluck('id')->all()
            );
        }

        return $roleIds;
    }
}
