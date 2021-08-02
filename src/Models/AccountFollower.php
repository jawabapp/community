<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;

class AccountFollower extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id', 'follower_account_id'
    ];

    /**
     * Get the user that owns the contact.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Get the user that owns the contact.
     */
    public function follower()
    {
        return $this->belongsTo(Account::class, 'follower_account_id');
    }

}
