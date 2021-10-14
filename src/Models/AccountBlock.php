<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Traits\HasDynamicRelation;

class AccountBlock extends Model
{
    use HasDynamicRelation;

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
        return $this->belongsTo(CommunityFacade::getUserClass(), 'account_id');
    }

    /**
     * Get the user that owns the contact.
     */
    public function block()
    {
        return $this->belongsTo(CommunityFacade::getUserClass(), 'block_account_id');
    }
}
