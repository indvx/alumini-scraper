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
        if (! Schema::hasTable('rfps')) {
            Schema::create('rfps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
                $table->foreignId('rfps_platform_id')->nullable()->constrained('rfps_platforms')->nullOnDelete();
                $table->string('project_id')->nullable()->index();
                $table->string('private_project_id')->nullable();
                $table->string('reference_id')->nullable()->index();
                $table->text('title');
                $table->text('description')->nullable();
                $table->string('department')->nullable();
                $table->timestamp('date_open')->nullable();
                $table->timestamp('date_close')->nullable();
                $table->boolean('is_public_award')->default(false);
                $table->string('status')->default('open')->index();
                $table->string('source')->default('bonfire')->index();
                $table->text('portal_url')->nullable();
                $table->text('opportunity_url')->nullable();
                $table->json('raw_data')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfps');
    }
};
