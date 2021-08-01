<?php

namespace Jawabapp\Community\Models\Post;

use Jawabapp\Community\Models\Post;
use Illuminate\Http\UploadedFile;
use Storage;

class Gif extends Post
{

    public static $post_path =  'post' . DIRECTORY_SEPARATOR . 'gif';

    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $node) {

            if ($node->isDirty('content')) {
                if ($node->getAttribute('content') instanceof UploadedFile) {

                    if ($node->getOriginal('content')) {
                        $toDelete = self::$post_path . str_replace(
                            Storage::url(self::$post_path),
                            '',
                            $node->getOriginal('content')
                        );
                        Storage::delete($toDelete);
                    }

                    $node->setAttribute('content', Storage::url($node->getAttribute('content')->store(self::$post_path . '/' . date('Y/m/d'))));
                }
            }
        });
    }

    public function getMorphClass()
    {
        return Post::class;
    }

    public function draw()
    {
        return view('admin.posts.types.gif')->with('post', $this);
    }
}
