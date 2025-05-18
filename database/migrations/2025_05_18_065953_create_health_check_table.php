<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_check', function (Blueprint $table) {
            $table->id();
            $table->string('api')->nullable();
            $table->string('db_read')->nullable();
            $table->string('db_write')->nullable();
            $table->string('cron_last_run')->nullable();
            $table->integer('uptime_seconds')->nullable();
            $table->integer('memory_usage_mb')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_check');
    }
};