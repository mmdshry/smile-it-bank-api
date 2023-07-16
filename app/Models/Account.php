<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the customer that owns the account.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the transfers associated with the account.
     *
     * @return HasMany
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class)
            ->where(function ($query) {
                $query->where('source_account_id', $this->id)
                    ->orWhere('destination_account_id', $this->id);
            });
    }

    /**
     * Get the incoming transfers associated with the account.
     *
     * @return HasMany
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'destination_account_id');
    }

    /**
     * Get the outgoing transfers associated with the account.
     *
     * @return HasMany
     */
    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'source_account_id');
    }
}
