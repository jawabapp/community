<?php

namespace JawabApp\CloudMessaging\Models\Post;

use App\Models\Post;
use App\Plugins\ImagePlugin;
use Illuminate\Http\UploadedFile;
use Storage;
use Image as FacadeImage;

class Image extends Post
{

    public static $post_path =  'post' . DIRECTORY_SEPARATOR . 'image';

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

                    $src = $node->getAttribute('content');

                    $image = FacadeImage::make($src);

                    $path = self::$post_path . '/' . date('Y/m/d');

                    $original = $src->store($path);

                    $thumbnail = ImagePlugin::resize($src, $path, 100, 100, $original);

                    $node->setAttribute('extra_info', [
                        'height' => $image->getHeight(),
                        'width' => $image->getWidth(),
                        'thumbnail' => Storage::url($thumbnail),
                    ]);

                    $node->setAttribute('content', Storage::url($original));
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
        return view('admin.posts.types.image')->with('post', $this);
    }
}
