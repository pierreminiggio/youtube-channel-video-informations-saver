<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use PierreMiniggio\GoogleTokenRefresher\AccessTokenProvider;
use PierreMiniggio\YoutubeAPI\YoutubeAPI;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository\YoutubeChannelRepository;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository\YoutubeVideoRepository;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube\YoutubeVideo;

class App
{
    public function run(): int
    {
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');

        $code = 0;

        if (empty($config['db'])) {
            echo 'Missing DB config';

            return $code;
        }

        if (empty($config['youtube_api'])) {
            echo 'Missing Youtube API config';

            return $code;
        }

        $databaseFetcher = new DatabaseFetcher((new DatabaseConnectionFactory())->makeFromConfig($config['db']));
        $channelRepository = new YoutubeChannelRepository($databaseFetcher);
        $videoRepository = new YoutubeVideoRepository($databaseFetcher);

        $provider = new AccessTokenProvider();
        $youtubeApiConfig = $config['youtube_api'];
        $accessToken = $provider->get(
            $youtubeApiConfig['client_id'],
            $youtubeApiConfig['client_secret'],
            $youtubeApiConfig['refresh_token']
        );

        $channelIds = $channelRepository->findAll();
        
        foreach ($channelIds as $channelId) {
            echo PHP_EOL . PHP_EOL . 'Channel : ' . $channelId;

            $youtubeApi = new YoutubeAPI($accessToken);

            $videoIds = $youtubeApi->getMostRecentVideoIdsForChannel($channelId, 3);
            
            foreach ($videoIds as $videoId) {
                echo PHP_EOL . 'Inserting/updating ' . $videoId . ' from channel ' . $channelId . ' ...';

                $youtubeVideo = YoutubeVideo::makeFromYoutubeAPIYoutubeVideo($youtubeApi->getVideoDetails($videoId));
                $videoRepository->addIfMissing($youtubeVideo);
                echo PHP_EOL . $youtubeVideo->getId() . ' inserted/updated !';
            }
        }

        echo PHP_EOL . PHP_EOL . 'Done !';

        return 0;
    }
}
