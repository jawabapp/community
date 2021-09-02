<?php

namespace Jawabapp\Community\Models\Post;

use App\Jobs\CompressVideoJob;
use Jawabapp\Community\Models\Post;
use Illuminate\Http\UploadedFile;
use Storage;
use Jawabapp\Community\Traits\HasDynamicRelation;

class Video extends Post
{

    public static $post_path =  'post' . DIRECTORY_SEPARATOR . 'video';

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $node) {

            $empty_compress = empty($node->getAttribute('extra_info')['compress']);

            if ($node->isDirty('content') && $empty_compress) {
                if ($node->getAttribute('content') instanceof UploadedFile) {

                    if ($node->getOriginal('content')) {
                        $toDelete = self::$post_path . str_replace(
                            Storage::url(self::$post_path),
                            '',
                            $node->getOriginal('content')
                        );
                        Storage::delete($toDelete);
                    }

                    $src = $node->getAttribute('content');

                    $path = self::$post_path . '/' . date('Y/m/d');

                    $original = $src->store($path);

                    $node->setAttribute('content', Storage::url($original));
                }
            }
        });

        static::saved(function (self $node) {

            $empty_compress = empty($node->getAttribute('extra_info')['compress']);

            if ($node->isDirty('content') && $empty_compress) {
                CompressVideoJob::dispatch($node);
            }
        });
    }

    public function getMorphClass()
    {
        return Post::class;
    }

    public function draw()
    {
        return view('community::admin.posts.types.video')->with('post', $this);
    }
}
