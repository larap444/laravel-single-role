<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     */
    public function scopeRole(Builder $builder, $role): Builder
    {
        return $builder->where('role_id', $role);
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
            if (strpos($role, $delimiter) !== false) {
                $role = explode($delimiter, $role);
            } else {
                $role = Role::query()->where('name', $role)->first();

                if (null === $role) {
                    return false;
                }

                return $currentRole === $role->getKey();
            }
        }

        if (is_array($role)) {
            foreach ($role as $item) {
                if (is_numeric($item) && (int) $item === $currentRole) {
                    return true;
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
     * @return $this
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function attachRole($role)
    {
        $this->update(['role_id' => $this->parseRole($role)]);

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

    /**
     * @param mixed $role
     *
     * @return int|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function parseRole($role)
    {
        if (null === $role) {
            return null;
        }

        if (is_string($role) && !is_numeric($role)) {
            $role = Role::query()->where('name', $role)->firstOrFail()->getKey();
        } elseif ($role instanceof Model) {
            $role = $role->getKey();
        }

        return (int) $role;
    }
}
