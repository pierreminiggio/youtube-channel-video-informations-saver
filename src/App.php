<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver;

use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository\YoutubeVideoRepository;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube\LatestVideosFetcher as LatestYoutubeVideoFetcher;

class App
{
    public function run(): int
    {
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');

        $lastestYoutubeVideosFetcher = new LatestYoutubeVideoFetcher();

        if ($hasDB = ! empty($config['db'])) {
            $repository = new YoutubeVideoRepository((new DatabaseConnectionFactory())->makeFromConfig($config['db']));
        }
    
        foreach ($config['groups'] as $group) {

            $youtubeChannel = $group['youtube'];
            echo PHP_EOL . PHP_EOL . 'Channel : ' . $youtubeChannel;

            $youtubeVideos = array_reverse($lastestYoutubeVideosFetcher->fetch($youtubeChannel));

            foreach ($youtubeVideos as $youtubeVideo) {
                if ($hasDB) {
                    echo PHP_EOL . 'Inserting/updating ' . $youtubeVideo->getId() . ' from channel ' . $youtubeVideo->getChannel() . ' ...';
                    $repository->addIfMissing($youtubeVideo);
                    echo PHP_EOL . $youtubeVideo->getId() . ' inserted/updated !';
                }
            }
        }

        echo PHP_EOL . PHP_EOL . 'Done !';

        return 0;
    }
}
