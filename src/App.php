<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver;

use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube\LatestVideosFetcher as LatestYoutubeVideoFetcher;

class App
{
    public function run(): int
    {
        $config = require(getcwd() . DIRECTORY_SEPARATOR . 'config.php');

        $lastestYoutubeVideosFetcher = new LatestYoutubeVideoFetcher();

    
        foreach ($config['groups'] as $group) {

            $youtubeChannel = $group['youtube'];

            $youtubeVideos = array_reverse($lastestYoutubeVideosFetcher->fetch($youtubeChannel));

            foreach ($youtubeVideos as $youtubeVideo) {
                var_dump($youtubeVideo);
            }
        }

        return 0;
    }
}
