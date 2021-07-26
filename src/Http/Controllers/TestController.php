<?php


namespace Jawabapp\Community\Http\Controllers;


class TestController extends Controller
{
    public function test() {
        return view('community::test.index');
    }
}
