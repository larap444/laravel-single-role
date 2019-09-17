<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePermissionsTable
 */
class CreatePermissionsTable extends Migration
{
    /**
     * @var string
     */
    protected $table;

    /**
     * CreatePermissionsTable constructor.
     */
    public function __construct()
    {
        $this->table = Config::get('single-role.tables.permissions');
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists($this->table);
    }
}
