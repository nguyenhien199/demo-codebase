<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_by_id')) {
                $table->unsignedInteger('deleted_by_id');
            }
            if (!Schema::hasColumn('users', 'deleted_by_name')) {
                $table->string('deleted_by_name',45);
            }

            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('users', function (Blueprint $table) {
            $fields = [
                'deleted_by_id',
                'deleted_by_name',
                'deleted_at',

            ];
            foreach ($fields as $field) {
                if (Schema::hasColumn('users', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }
};
