<?php

namespace Jawabapp\Community\Models;

use Illuminate\Database\Eloquent\Model;
use Jawabapp\Community\Traits\HasDynamicRelation;

class PostReport extends Model
{
    use HasDynamicRelation;

    const This_Is_A_Repost = 1;
    const I_Dont_Like_This  = 2;
    const Spam = 3;
    const Pronnography = 4;
    const Hatred = 5;
    const Self_harm = 6;
    const Violent = 7;
    const Child_Abuse = 8;
    const Illegal_Activities = 9;
    const Copyright_And_Trademark_Infringement = 10;

    const REPORT_TYPES = [
        self::This_Is_A_Repost => 'This is a repost',
        self::I_Dont_Like_This => 'I don’t like this',
        self::Spam => 'Spam',
        self::Pronnography => 'Pronnography',
        self::Hatred => 'Hatred',
        self::Self_harm => 'Self-harm',
        self::Violent => 'Violent',
        self::Child_Abuse => 'Child Abuse',
        self::Illegal_Activities => 'Illegal activities e.g. Drugs uses',
        self::Copyright_And_Trademark_Infringement => 'Copyright and trademark infringement',
    ];

    protected $fillable = [
        'post_id',
        'account_id',
        'report'
    ];

    public function getConnectionName()
    {
        return config('community.database_connection');
    }
}
