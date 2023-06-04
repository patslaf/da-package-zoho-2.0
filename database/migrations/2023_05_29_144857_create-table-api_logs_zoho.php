<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableApiLogsZoho extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_logs_zoho', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('pk', 100)->nullable();
            $table->string('source', 255)->nullable();
            $table->string('request_url', 255);
            $table->string('type', 50)->nullable();
            $table->text('data')->nullable();
            $table->text('response')->nullable();
            $table->integer('status_code')->nullable();
            $table->boolean('outcome')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_logs_zoho');
    }
}
