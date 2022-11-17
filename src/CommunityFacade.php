<?php

namespace Jawabapp\Community;

use Illuminate\Support\Facades\Facade;

/**
 * @method static getUserClass()
 * @method static getLoggedInUser()
 * @method static createPostWithTag(Request $request)
 * @method static createPost(Request $request)
 *
 * @see \Jawabapp\Community\CommunityService
 */
class CommunityFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'community';
    }
}
