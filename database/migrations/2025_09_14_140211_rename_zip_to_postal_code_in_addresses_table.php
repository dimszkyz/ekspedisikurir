<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('addresses', function (Blueprint $table) {
        if (Schema::hasColumn('addresses', 'zip')) {
            $table->renameColumn('zip', 'postal_code');
        }
    });
}

public function down()
{
    Schema::table('addresses', function (Blueprint $table) {
        if (Schema::hasColumn('addresses', 'postal_code')) {
            $table->renameColumn('postal_code', 'zip');
        }
    });
}

};
