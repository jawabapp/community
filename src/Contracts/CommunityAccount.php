<?php

namespace Jawabapp\Community\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CommunityAccount
{
    public static function getLoggedInUser();
    public function getAccount($account_id);
}
