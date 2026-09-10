<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('location_searches')) {
            Schema::create('location_searches', function (Blueprint $table) {
                $table->id();
                $table->string('query')->index();
                $table->text('display_name');
                $table->unsignedBigInteger('area_id')->nullable();
                $table->string('place_name')->nullable();
                $table->string('state', 100)->nullable();
                $table->string('country', 100)->nullable();
                $table->unsignedInteger('total_found')->default(0);
                $table->timestamp('searched_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('location_searches');
    }
};
