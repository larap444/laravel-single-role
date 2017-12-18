<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Config;

/**
 * Class Permission
 *
 * @package McMatters\Models\SingleRole
 */
class Permission extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'permissions';

    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Permission constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get('single-role.tables.permissions'));

        parent::__construct($attributes);
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'permission_role',
            'role_id',
            'permission_id',
            'roles'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            Config::get('single-role.models.user'),
            'permission_user',
            'user_id',
            'permission_id',
            'users'
        );
    }
}
