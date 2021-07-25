<?php

namespace Jawabapp\Community;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jawabapp\Community\Skeleton\SkeletonClass
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
