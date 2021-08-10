<?php
/**
 * Created by PhpStorm.
 * User: qanah
 * Date: 10/17/18
 * Time: 5:55 PM
 */

namespace Jawabapp\Community\Plugins;

use Illuminate\Support\Facades\Storage;

class VideoPlugin
{

    public static function compress($videoUrl, $with_thumbnail = false) {

        $path_parts = pathinfo($videoUrl);

        $dirname = $path_parts['dirname'] ?? '';
        $basename = $path_parts['basename'] ?? '';
        $extension = $path_parts['extension'] ?? '';
        $filename = $path_parts['filename'] ?? '';

        $bucketUrl = str_replace('AWS_BUCKET_URL', '', Storage::url('AWS_BUCKET_URL'));

        $path = str_replace($bucketUrl, '', $dirname);

        $original = $path .'/'. $basename;

//        $compress = $path . "/compress/{$filename}.mkv";
//
//        \FFMpeg::fromDisk('minio')
//            ->open($original)
//            ->export()
//            ->toDisk('minio')
//            ->inFormat(new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))
//            ->save($compress);

        if($with_thumbnail) {
            $thumbnail = $path . "/thumbnails/{$filename}.png";

            \FFMpeg::fromDisk('minio')
                ->open($original)
                ->getFrameFromSeconds(1)
                ->export()
                ->toDisk('minio')
                ->save($thumbnail);

            $image = \Image::make(Storage::url($thumbnail));

            return [
                Storage::url($original), // Storage::url($compress),
                Storage::url($thumbnail),
                $image->getHeight(),
                $image->getWidth(),
            ];
        }

        return [
            Storage::url($original) // Storage::url($compress)
        ];
    }

}
