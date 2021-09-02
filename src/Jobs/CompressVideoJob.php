<?php

namespace Jawabapp\Community\Jobs;

use Jawabapp\Community\Models\Post\Video;
use Jawabapp\Community\Plugins\VideoPlugin;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class CompressVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $videoPost;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 0;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * CompressVideoJob constructor.
     * @param Video $videoPost
     */
    public function __construct(Video $videoPost)
    {
        $this->videoPost = $videoPost;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('[Compress Video Job] Handle');

        if (empty($this->videoPost)) {
            return;
        }

        try {

            $original = $this->videoPost->content;

            if ($original) {

                list($compress, $thumbnail, $height, $width) = VideoPlugin::compress($original, true);

                $this->videoPost->update([
                    // 'content' => $compress,
                    'extra_info' => [
                        'original' => $original,
                        'compress' => $compress,
                        'thumbnail' => $thumbnail,
                        'height' => $height,
                        'width' => $width,
                    ]
                ]);

                $this->videoPost->resetCache();
            }
        } catch (\Exception $exception) {
        }
    }
}
