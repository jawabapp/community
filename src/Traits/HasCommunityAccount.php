<?php

namespace Jawabapp\Community\Traits;

trait HasCommunityAccount
{
    public function getDefaultAccount()
    {
        return $this->accounts()->where('accounts.default', '=', 1)->first();
    }

    public function getAccount($account_id)
    {
        return $account_id;
    }

    public function getAccountUser()
    {
        return $this->user()->first();
    }
}
