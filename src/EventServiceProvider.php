<?php

namespace Jawabapp\Community;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Jawabapp\Community\Events;

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
        if (config('community.listeners.post_reply')) {
            Event::listen(
                Events\PostReply::class,
                config('community.listeners.post_reply')
            );
        }

        if (config('community.listeners.post_interaction')) {
            Event::listen(
                Events\PostInteraction::class,
                config('community.listeners.post_interaction')
            );
        }

        if (config('community.listeners.post_mention')) {
            Event::listen(
                Events\PostMention::class,
                config('community.listeners.post_mention')
            );
        }
    }
}
