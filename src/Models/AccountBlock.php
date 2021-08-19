<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBlock extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'block_account_id'
    ];

    /**
     * Get the user that owns the contact.
     */
    public function account()
    {
        return $this->belongsTo(config('community.user_class'), 'account_id');
    }

    /**
     * Get the user that owns the contact.
     */
    public function block()
    {
        return $this->belongsTo(config('community.user_class'), 'block_account_id');
    }
}
