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
        Schema::create('zoho_oauthtoken', function (Blueprint $table) {
            $table->string('id');
            $table->string('user_mail');
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('access_token')->nullable();
            $table->string('grant_token')->nullable();
            $table->string('expiry_time')->nullable();
            $table->string('redirect_url')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_oauthtoken');
    }
};
