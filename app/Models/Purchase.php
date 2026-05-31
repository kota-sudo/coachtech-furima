<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    public $timestamps = false;

    public const PAYMENT_CONVENIENCE = 1;

    public const PAYMENT_CARD = 2;

    public const PAYMENT_METHODS = [
        self::PAYMENT_CONVENIENCE => 'コンビニ支払い',
        self::PAYMENT_CARD => 'カード支払い',
    ];

    protected $fillable = [
        'user_id',
        'item_id',
        'postal_code',
        'address',
        'building',
        'payment_method',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? '—';
    }
}
