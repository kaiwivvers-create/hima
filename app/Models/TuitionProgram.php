<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TuitionProgram extends Model
{
    /**
     * Billing plans available across the app. A program only offers a plan
     * when a price is set for it (see availablePlans()).
     *
     * @var array<string, array{label:string, count:int, interval:int, column:string}>
     */
    public const PLANS = [
        'monthly' => ['label' => 'Monthly (12x)', 'count' => 12, 'interval' => 1, 'column' => 'monthly_amount'],
        'bi_monthly' => ['label' => 'Every 2 months (6x)', 'count' => 6, 'interval' => 2, 'column' => 'bi_monthly_amount'],
        'triannual' => ['label' => '3x per year', 'count' => 3, 'interval' => 4, 'column' => 'triannual_amount'],
        'quarterly' => ['label' => '4x per year (quarterly)', 'count' => 4, 'interval' => 3, 'column' => 'quarterly_amount'],
        'yearly' => ['label' => 'Yearly (1x)', 'count' => 1, 'interval' => 12, 'column' => 'yearly_amount'],
    ];

    protected $fillable = [
        'slug',
        'name',
        'monthly_amount',
        'bi_monthly_amount',
        'triannual_amount',
        'quarterly_amount',
        'yearly_amount',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'decimal:2',
            'bi_monthly_amount' => 'decimal:2',
            'triannual_amount' => 'decimal:2',
            'quarterly_amount' => 'decimal:2',
            'yearly_amount' => 'decimal:2',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_tuition_program', 'tuition_program_id', 'student_id')
            ->withPivot('annual_amount');
    }

    /**
     * Price charged per installment for the given plan key, if set.
     */
    public function planPrice(string $plan): ?float
    {
        $config = self::PLANS[$plan] ?? null;
        if (!$config) {
            return null;
        }

        $value = $this->{$config['column']};
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    /**
     * Plans this program actually offers (those with a price set).
     *
     * @return array<string, array{label:string, count:int, interval:int, column:string, price:float}>
     */
    public function availablePlans(): array
    {
        $plans = [];
        foreach (self::PLANS as $key => $config) {
            $price = $this->planPrice($key);
            if ($price !== null && $price > 0) {
                $plans[$key] = $config + ['price' => $price];
            }
        }

        return $plans;
    }
}
