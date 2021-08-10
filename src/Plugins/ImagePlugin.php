<?php
/**
 * Created by PhpStorm.
 * User: qanah
 * Date: 10/17/18
 * Time: 5:55 PM
 */

namespace Jawabapp\Community\Plugins;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Storage;
use Image;

class ImagePlugin
{

    public static function resize(UploadedFile $src, $path, $width, $height, $original = null) {

        $extension = $src->getClientOriginalExtension();

        if($original) {
            $extension = pathinfo($original, PATHINFO_EXTENSION);
        }

        // Get the filepath of the request file (.tmp) and append .jpg
        $requestImagePath = $src->getRealPath() . "_{$width}x{$height}." . $extension;

        // Modify the image using intervention
        $interventionImage = Image::make($src)->resize($width, $height);

        // Save the intervention image over the request image
        $interventionImage->save($requestImagePath);

        $file = new File($requestImagePath);

        // Send the image to file storage
        $resize = Storage::put($path, $file);

        unlink($file->getRealPath());

        if($original) {

            $original = str_replace(".{$extension}", '', $original);

            $to = $original . "-{$width}x{$height}." . $extension;

            Storage::move($resize, $to);

            $resize = $to;

        }

        return $resize;

    }

    public static function deleteOldFiles($path, $files){

        if(!is_array($files)) {
            $files = [$files];
        }

        $toDelete = [];

        foreach ($files as $file) {
            $toDelete[] = $path . str_replace(Storage::url($path), '', $file);
        }

        Storage::delete($toDelete);
    }
}
