<?php

namespace Jawabapp\Community\Traits;

 trait HasCommunityAccount {

     public static function getActiveAccountId() {
        return auth()->id();
     }



 }
