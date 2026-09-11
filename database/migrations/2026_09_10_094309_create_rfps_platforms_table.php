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
        Schema::create('rfps_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('domain')->nullable();
            $table->string('url')->nullable();
            $table->string('platform_type')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('institution_type')->nullable();
            $table->string('coverage')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('requires_login')->default(false);
            // $table->string('discovery_method')->nullable();
            $table->timestamp('last_checked')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfps_platforms');
    }
};
