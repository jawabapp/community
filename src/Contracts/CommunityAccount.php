<?php

namespace Jawabapp\Community\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface CommunityAccount
{
    public function getDefaultAccount();
    public function getAccount($account_id);
    public function getAccountUser();
}
