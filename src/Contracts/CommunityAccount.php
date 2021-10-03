<?php

namespace Jawabapp\Community\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CommunityAccount
{
    public static function getDefaultAccount();
    public function getAccount($account_id);
}
