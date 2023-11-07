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
        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('send_code')->nullable();//TODO лишние поля
            $table->integer('get_code')->nullable();
            $table->string('id_sms')->nullable();
            $table->string('status')->nullable();
            $table->integer('lead_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('uuid')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('account_id')->nullable();
            $table->integer('subdomain')->nullable();
            $table->boolean('is_agreement')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docs');
    }
};
