<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver;

use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository\YoutubeVideoRepository;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube\LatestVideosFetcher as LatestYoutubeVideoFetcher;

class App
{
    public function run(): int
    {
        $config = require(getcwd() . DIRECTORY_SEPARATOR . 'config.php');

        $lastestYoutubeVideosFetcher = new LatestYoutubeVideoFetcher();

        if ($hasDB = ! empty($config['db'])) {
            $repository = new YoutubeVideoRepository((new DatabaseConnectionFactory())->makeFromConfig($config['db']));
        }
    
        foreach ($config['groups'] as $group) {

            $youtubeChannel = $group['youtube'];

            $youtubeVideos = array_reverse($lastestYoutubeVideosFetcher->fetch($youtubeChannel));

            foreach ($youtubeVideos as $youtubeVideo) {
                if ($hasDB) {
                    $repository->addIfMissing($youtubeVideo);
                }
            }
        }

        return 0;
    }
}
