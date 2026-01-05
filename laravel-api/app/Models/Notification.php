<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'transaction_id',
        'email',
        'message',
        'status',
        'error_message',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
