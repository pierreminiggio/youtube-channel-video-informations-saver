<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube;

use Exception;
use SimpleXMLElement;

class LatestVideosFetcher
{

    /**
     * @return YoutubeVideo[]
     * 
     * @throws Exception
     */
    public function fetch(string $username): array
    {
        $videos = [];

        $curl = curl_init();
        $url = 'https://www.youtube.com/feeds/videos.xml?user=' . $username;
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        $string = curl_exec($curl);

        $error = curl_error($curl);
        if (! empty($error)) {
            throw new Exception($error);
        }

        curl_close($curl);

        if ($string) {
            $videosXML = new SimpleXMLElement($string);

            $channelId = str_replace('yt:channel:', '', $videosXML->id);
            
            foreach ($videosXML->getNamespaces(true) as $alias => $namespace) {
                $videosXML->registerXPathNamespace($alias, $namespace);
            }
            
            foreach ($videosXML->entry as $videoXML) {
                $id = substr($videoXML->id, 9);
                $url = "https://www.youtube.com/watch?v=" . $id;
                foreach ($videoXML->xpath('media:group') as $mediaGroup) {
                    foreach ($mediaGroup->xpath('media:thumbnail') as $attribute) {
                        $thumbnail = $attribute->attributes()->url->__toString();
                    }
                    $title = $mediaGroup->xpath('media:title')[0]->__toString();
                    $description = $mediaGroup->xpath('media:description')[0]->__toString();
                }

                $videos[] = new YoutubeVideo($channelId, $id, $url, $thumbnail, $title, $description);
            }
        }

        return $videos;
    }
}
