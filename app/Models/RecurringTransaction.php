<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Frequency;
use Database\Factories\RecurringTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTransaction extends Model
{
    /** @use HasFactory<RecurringTransactionFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'amount',
        'description',
        'frequency',
        'start_date',
        'end_date',
        'next_run_date',
        'last_run_date',
        'runs_count',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'frequency' => Frequency::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'next_run_date' => 'date',
            'last_run_date' => 'date',
            'runs_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the owning user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated category.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get generated transactions.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get status label: Active, Paused, or Ended.
     */
    public function status(): string
    {
        if ($this->next_run_date === null) {
            return 'Ended';
        }

        if (! $this->is_active) {
            return 'Paused';
        }

        return 'Active';
    }
}
