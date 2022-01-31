<?php

namespace Jawabapp\Community;

use Jawabapp\Community\Events;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        if (config('community.listeners.create_post')) {
            Event::listen(
                Events\PostCreate::class,
                config('community.listeners.create_post')
            );
        }

        if (config('community.listeners.delete_post')) {
            Event::listen(
                Events\PostDelete::class,
                config('community.listeners.delete_post')
            );
        }

        if (config('community.listeners.create_comment')) {
            Event::listen(
                Events\CommentCreate::class,
                config('community.listeners.create_comment')
            );
        }

        if (config('community.listeners.delete_comment')) {
            Event::listen(
                Events\CommentDelete::class,
                config('community.listeners.delete_comment')
            );
        }

        if (config('community.listeners.create_post_interaction')) {
            Event::listen(
                Events\PostInteractionCreate::class,
                config('community.listeners.create_post_interaction')
            );
        }

        if (config('community.listeners.delete_post_interaction')) {
            Event::listen(
                Events\PostInteractionDelete::class,
                config('community.listeners.delete_post_interaction')
            );
        }

        if (config('community.listeners.post_mention')) {
            Event::listen(
                Events\PostMention::class,
                config('community.listeners.post_mention')
            );
        }

        if (config('community.listeners.like_account')) {
            Event::listen(
                Events\AccountLikeCreate::class,
                config('community.listeners.like_account')
            );
        }
    }
}
