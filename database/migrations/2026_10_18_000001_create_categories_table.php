<?php

declare(strict_types=1);

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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 60);
            $table->enum('type', ['income', 'expense']);
            $table->char('color', 7)->default('#1F5F4D');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'type', 'name']);
            $table->index(['user_id', 'is_archived']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
