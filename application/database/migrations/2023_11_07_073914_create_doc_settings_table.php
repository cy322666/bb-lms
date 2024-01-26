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
        Schema::create('doc_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('target_login')->nullable();
            $table->string('target_pass')->nullable();
            $table->string('target_sender')->nullable();
            $table->string('target_sender')->nullable();
            $table->integer('account_id')->nullable();
            $table->string('subdomain')->nullable();
            $table->string('text_sms', 500)->nullable();
            $table->integer('status_id_confirm')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_settings');
    }
};
