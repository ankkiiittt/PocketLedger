<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryType;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property CategoryType $type
 * @property string $color
 * @property bool $is_archived
 * @property CarbonImmutable|Carbon|null $created_at
 * @property CarbonImmutable|Carbon|null $updated_at
 * @property int|null $transactions_count
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    public const array PRESET_COLORS = [
        '#1F5F4D',
        '#B4432F',
        '#2F5F8A',
        '#B7791F',
        '#6B4E8F',
        '#3D8B8B',
        '#8A5A44',
        '#6F7D3A',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'color',
        'is_archived',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_archived' => 'boolean',
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
     * Get transactions for this category.
     *
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get budgets for this category.
     *
     * @return HasMany<Budget, $this>
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get recurring rules for this category.
     *
     * @return HasMany<RecurringTransaction, $this>
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * Scope query to active categories only.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope query to expense categories only.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Expense);
    }

    /**
     * Scope query to income categories only.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', CategoryType::Income);
    }

    /**
     * Check if category is used and therefore cannot be deleted.
     */
    public function isUsed(): bool
    {
        return $this->transactions()->exists() || $this->recurringTransactions()->exists();
    }

    /**
     * Check if category type is locked (cannot be changed).
     */
    public function isTypeLocked(): bool
    {
        return $this->transactions()->exists()
            || $this->recurringTransactions()->exists()
            || $this->budgets()->exists();
    }
}
