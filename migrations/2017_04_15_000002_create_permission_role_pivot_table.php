<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePermissionRolePivotTable
 */
class CreatePermissionRolePivotTable extends Migration
{
    /**
     * @var string
     */
    protected $table;

    /**
     * CreatePermissionRolePivotTable constructor.
     */
    public function __construct()
    {
        $this->table = Config::get('single-role.tables.permission_role');
    }

    /**
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->unsignedInteger('permission_id')->index();
            $table->unsignedInteger('role_id')->index();

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}
