<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('widget_type', ['chart', 'table', 'metric', 'text']);
            $table->json('widget_data')->nullable();
            $table->integer('position')->default(0);
            $table->enum('size', ['small', 'medium', 'large'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};