<?php


namespace Jawabapp\Community\Services;

class DeepLinkBuilder
{

    /**
     * https://firebase.google.com/docs/dynamic-links/rest
     * https://firebase.google.com/docs/reference/dynamic-links/link-shortener
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public static function generate(array $query, array $info = [])
    {
        $deep_link = '';

        try {
            $class = config('community.deep_link.class');
            $action = config('community.deep_link.action');
            if ($class && class_exists($class)) {
                $deep_link = $class::$action($query, $info);
            }
        } catch (Exception $e) {
        }

        return $deep_link;
    }
}
