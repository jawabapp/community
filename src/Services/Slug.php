<?php

/**
 * Created by PhpStorm.
 * User: ibraheemqanah
 * Date: 2019-05-21
 * Time: 14:52
 */

namespace Jawabapp\Community\Services;


use Illuminate\Support\Str;
use Jawabapp\Community\CommunityFacade;

class Slug
{
    /**
     * @param $title
     * @param int $id
     * @return string
     */
    public function createSlug($title, $id = 0)
    {

        if($title) {
            // Normalize the title
            $slug = $this->accountSlug($title);

            // Get any that could possibly be related.
            // This cuts the queries down by doing it once.
            $allSlugs = $this->getRelatedSlugs($slug, $id);

            // If we haven't used it before then we are all good.
            if (!$allSlugs->contains('slug', strtolower($slug))) {
                return $slug;
            }

            // Just append numbers like a savage until we find not used.
            for ($i = 1; $i <= 5; $i++) {
                $newSlug = $slug . '-' . $this->generateRandomString();
                if (! $allSlugs->contains('slug', strtolower($newSlug))) {
                    return $newSlug;
                }
            }
        }

        return '@' . uniqid();
    }

    protected function getRelatedSlugs($slug, $id = 0)
    {
        return CommunityFacade::getUserClass()::select(\DB::raw('LOWER(`slug`) AS slug'))->whereRaw("LOWER(`slug`) like ?", [strtolower($slug) . '%'])
            ->where('id', '<>', $id)
            ->get();
    }

    /**
     * @param int $num
     * @return string
     */
    private function generateRandomString($num = 5)
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, $num) as $k) $rand .= $seed[$k];

        return $rand;
    }

    private function accountSlug($title, $separator = '-', $language = 'en')
    {
        $title = $language ? Str::ascii($title, $language) : $title;

        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator . 'at' . $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', $title);

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        $title = trim($title, $separator);

        $title = str_replace($separator, ' ', $title);

        $title = ucwords($title);

        $title = str_replace(' ', '', $title);

        return '@' . $title;
    }
}
