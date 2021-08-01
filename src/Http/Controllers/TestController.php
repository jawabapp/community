<?php

namespace JawabApp\Community\Http\Controllers;

class TestController extends Controller
{
    public function test()
    {
        return view('community::test.index');
    }

    public function testApi()
    {
        return [
            'test' => 'ok'
        ];
    }
}
