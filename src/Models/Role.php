<?php

declare(strict_types = 1);

namespace McMatters\SingleRole\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use McMatters\SingleRole\Traits\HasPermission;
use const false;

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
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Role constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get('single-role.tables.roles'));

        parent::__construct($attributes);
    }
}
