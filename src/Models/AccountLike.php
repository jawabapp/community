<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Traits\HasDynamicRelation;

class AccountLike extends Model
{
    use HasDynamicRelation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id', 'liked_account_id'
    ];

    public function getConnectionName()
    {
        return config('community.database_connection');
    }

    /**
     * Get the user that owns the contact.
     */
    public function account()
    {
        return $this->belongsTo(CommunityFacade::getUserClass(), 'account_id');
    }

    /**
     * Get the user that owns the contact.
     */
    public function liked_account()
    {
        return $this->belongsTo(CommunityFacade::getUserClass(), 'liked_account_id');
    }
}
