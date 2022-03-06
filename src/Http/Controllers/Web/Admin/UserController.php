<?php

namespace Jawabapp\Community\Http\Controllers\Web\Admin;

use Jawabapp\Community\CommunityFacade;
use Jawabapp\Community\Models\Account;
use Illuminate\Http\Request;
use Jawabapp\Community\Models\Post;
use Illuminate\Support\Facades\Session;
use Jawabapp\Community\Http\Controllers\Controller;
use Jawabapp\Community\Http\Requests\Post\CreateRequest;
use Jawabapp\Community\Http\Requests\Post\UpdateRequest;

class UserController extends Controller
{

    public function search(Request $request)
    {
        $userMobile = CommunityFacade::getUserClass()::query();
        $userMobile->where('phone','LIKE',"%{$request->phone}%");
        return $userMobile->paginate(16);
    }

}
