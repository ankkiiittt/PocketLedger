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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('description', 255)->nullable();
            $table->foreignId('recurring_transaction_id')->nullable()->constrained('recurring_transactions')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'category_id', 'transaction_date']);
            $table->unique(['recurring_transaction_id', 'transaction_date']);
        });

        DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_amount_positive CHECK (amount > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
