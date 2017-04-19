<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Models;

use Illuminate\Database\Eloquent\Model;
use McMatters\SingleRole\Traits\HasPermission;

/**
 * Class Role
 *
 * @package McMatters\Models\SingleRole
 */
class Role extends Model
{
    use HasPermission;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'roles';

    /**
     * @var array
     */
    protected $fillable = ['name'];
}
