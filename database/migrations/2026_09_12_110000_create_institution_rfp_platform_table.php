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
        if (! Schema::hasTable('institution_rfp_platform')) {
            Schema::create('institution_rfp_platform', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
                $table->foreignId('rfps_platform_id')->constrained('rfps_platforms')->cascadeOnDelete();
                $table->integer('confidence')->nullable()->default(100);
                $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
                $table->string('discovery_method')->nullable();
                $table->string('source_title')->nullable();
                $table->text('source_url')->nullable();
                $table->timestamp('first_verified_at')->nullable();
                $table->timestamp('last_verified_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['institution_id', 'rfps_platform_id'], 'uix_inst_rfp_platform');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_rfp_platform');
    }
};
