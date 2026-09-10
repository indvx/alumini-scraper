<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('institutions')) {
            Schema::create('institutions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('search_id')->nullable()->constrained('location_searches')->nullOnDelete();
                $table->string('osm_id', 100)->nullable();
                $table->string('osm_type', 50)->nullable();
                $table->string('name')->default('Unnamed')->index();
                $table->string('type', 100)->default('school')->index();
                $table->double('latitude')->nullable();
                $table->double('longitude')->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('postcode', 50)->nullable()->index();
                $table->string('phone', 100)->nullable();
                $table->string('website')->nullable();
                $table->boolean('is_checked')->default(false);
                $table->timestamps();

                $table->unique(['latitude', 'longitude'], 'uix_institution_lat_lon');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
