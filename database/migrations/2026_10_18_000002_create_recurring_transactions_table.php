<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('description', 255)->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date')->nullable();
            $table->date('last_run_date')->nullable();
            $table->unsignedInteger('runs_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'next_run_date']);
            $table->index('user_id');
        });

        DB::statement('ALTER TABLE recurring_transactions ADD CONSTRAINT chk_recurring_amount_positive CHECK (amount > 0)');
        DB::statement('ALTER TABLE recurring_transactions ADD CONSTRAINT chk_recurring_end_date CHECK (end_date IS NULL OR end_date >= start_date)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
