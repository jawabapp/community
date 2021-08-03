<?php


namespace Jawabapp\Community\Services;

// use App\Models\Mongo\DeepLink;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Exception;

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
    public static function generate(Request $request, $domainUriPrefix = 'https://jawab.page.link', $service_id = 0) {

        $sources = collect(config('sources'));
        $source = $sources->where('id', $service_id)->first();
        $source = $source ?? $sources->first();

        $client = new Client();

        $link_id = uniqid('link-id-');
        $link = preg_replace_callback('/[اأإء-ي]+/ui', function($matches) { return urlencode($matches[0]); }, $request->get('link'));

        $utm_source = $source['source'] ?? $request->get('analyticsUtmSource');
        $utm_medium = $request->get('analyticsUtmMedium');
        $utm_campaign = $request->get('analyticsUtmCampaign');

        $utm_analytics = [];

        if($utm_medium) {
            $utm_analytics['app_medium'] = $utm_medium;
        }

        if($utm_campaign) {
            $utm_analytics['app_campaign'] = $utm_campaign;
        }

        $analytics_query = http_build_query(array_merge([
            'link_id' => $link_id,
            'app_source' => $utm_source,
        ], $utm_analytics));

        if (strpos($link, "?") !== false){
            $link = "{$link}&{$analytics_query}";
        } else {
            $link = "{$link}?{$analytics_query}";
        }

        $json = [
            "dynamicLinkInfo" => [
                "domainUriPrefix" => $domainUriPrefix,
                "link" => $link,
                "androidInfo" => [
                    "androidPackageName" => "app.jawab.chat",
                ],
                "iosInfo" => [
                    "iosBundleId" => "app.jawab.chat",
                    "iosCustomScheme" => "app.jawab.chat",
                    "iosAppStoreId" => "1445999629"
                ],
//                "navigationInfo" => [
//                    "enableForcedRedirect" => '1',
//                ],
            ],
            "suffix" => [
                "option" => "UNGUESSABLE" // or "SHORT"
            ]
        ];

        if($domainUriPrefix == 'https://jawab.page.link') {
            $json['dynamicLinkInfo']['navigationInfo']['enableForcedRedirect'] = '1';
        }

        if ($request->has('socialTitle')){
            $json['dynamicLinkInfo']['socialMetaTagInfo']['socialTitle'] = $request->get('socialTitle');
        }
        if ($request->has('socialDescription')){
            $json['dynamicLinkInfo']['socialMetaTagInfo']['socialDescription'] = $request->get('socialDescription');
        }
        if ($request->has('socialImageLink')){
            $json['dynamicLinkInfo']['socialMetaTagInfo']['socialImageLink'] = $request->get('socialImageLink');
        }
        if ($utm_source){
            $json['dynamicLinkInfo']['analyticsInfo']['googlePlayAnalytics']['utmSource'] = $utm_source;
        }
        if ($utm_medium){
            $json['dynamicLinkInfo']['analyticsInfo']['googlePlayAnalytics']['utmMedium'] = $utm_medium;
        }
        if ($utm_campaign){
            $json['dynamicLinkInfo']['analyticsInfo']['googlePlayAnalytics']['utmCampaign'] = $utm_campaign;
        }
//        if ($request->has('analyticsUtmTerm')){
//            $json['dynamicLinkInfo']['analyticsInfo']['googlePlayAnalytics']['utmTerm'] = $request->get('analyticsUtmTerm');
//        }
//        if ($request->has('analyticsUtmContent')){
//            $json['dynamicLinkInfo']['analyticsInfo']['googlePlayAnalytics']['utmContent'] = $request->get('analyticsUtmContent');
//        }
        if ($request->has('analyticsGClId')){
            $json['dynamicLinkInfo']['analyticsInfo']['googlePlayAnalytics']['gclid'] = $request->get('analyticsGClId');
        }
        if ($request->has('analyticsItunesAT')){
            $json['dynamicLinkInfo']['analyticsInfo']['itunesConnectAnalytics']['at'] = $request->get('analyticsItunesAT');
        }
        if ($request->has('analyticsItunesCT')){
            $json['dynamicLinkInfo']['analyticsInfo']['itunesConnectAnalytics']['ct'] = $request->get('analyticsItunesCT');
        }
        if ($request->has('analyticsItunesMT')){
            $json['dynamicLinkInfo']['analyticsInfo']['itunesConnectAnalytics']['mt'] = $request->get('analyticsItunesMT', '8');
        }
        if ($request->has('analyticsItunesPT')){
            $json['dynamicLinkInfo']['analyticsInfo']['itunesConnectAnalytics']['pt'] = $request->get('analyticsItunesPT');
        }

        $response = $client->post("https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=AIzaSyCsDuCMzWQuTpt6rSe6ZnvP-aImom4S9Pc", [
            RequestOptions::JSON => $json
        ]);

        if($response->getStatusCode() != 200) {
            throw new Exception('The remote endpoint could not be called, or the response it returned was invalid.');
        }

        try {
            $content = json_decode($response->getBody()->getContents(), true);

            if(!empty($content['shortLink'])) {

                $utmContent = $request->get('analyticsUtmContent');

                if(is_string($utmContent)) {
                    $utmContent = json_decode($utmContent, true);
                }

                // DeepLink::create([
                //     'link_id' => $link_id,
                //     'link' => $content['shortLink'],
                //     'info' => $json,
                //     'service_id' => $service_id,
                //     'content' => $utmContent
                // ]);

                return $content['shortLink'];
            }

            throw new Exception('invalid short Link');

        } catch (Exception $e) {
            throw $e;
        }
    }

}
