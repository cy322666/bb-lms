<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doc_settings', function (Blueprint $table) {

            $table->dropColumn('target_login');
            $table->dropColumn('target_pass');
            $table->dropColumn('target_sender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doc_settings', function (Blueprint $table) {

            $table->string('target_login')->nullable();
            $table->string('target_pass')->nullable();
            $table->string('target_sender')->nullable();
        });
    }
};
