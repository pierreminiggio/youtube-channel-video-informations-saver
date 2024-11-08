<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use PierreMiniggio\GoogleTokenRefresher\AccessTokenProvider;
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
        $accessToken = $provider->get($youtubeApiConfig['client_id'], $youtubeApiConfig['client_secret'], $youtubeApiConfig['refresh_token']);

        $channelIds = $channelRepository->findAll();
        
        foreach ($channelIds as $channelId) {

            echo PHP_EOL . PHP_EOL . 'Channel : ' . $channelId;

            $channelVideosCurl = curl_init();
            curl_setopt_array($channelVideosCurl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://www.googleapis.com/youtube/v3/search?channelId=' . $channelId . '&part=id&order=date&maxResults=3'
            ]);
            $authorization = "Authorization: Bearer " . $accessToken;
            curl_setopt($channelVideosCurl, CURLOPT_HTTPHEADER, ['Content-Type: application/json' , $authorization]);

            $channelVideosCurlResult = curl_exec($channelVideosCurl);
            $channelVideosJsonResponse = json_decode($channelVideosCurlResult, true);

            if (! empty($channelVideosJsonResponse['error'])) {
                echo 'Error ' . $channelVideosJsonResponse['error']['code'] . ': ' . $channelVideosJsonResponse['error']['message'];

                return $code;
            }

            $videoIds = array_filter(array_map(
                fn ($channelVideoJsonResponse) => $channelVideoJsonResponse['id']['videoId'] ?? null,
                $channelVideosJsonResponse['items']
            ), fn ($channelVideoId) => $channelVideoId !== null);
            
            foreach ($videoIds as $videoId) {

                echo PHP_EOL . 'Inserting/updating ' . $videoId . ' from channel ' . $channelId . ' ...';

                $videoCurl = curl_init();
                curl_setopt_array($videoCurl, [
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => 'https://www.googleapis.com/youtube/v3/videos?id=' . $videoId . '&part=snippet'
                ]);
                $authorization = "Authorization: Bearer " . $accessToken;
                curl_setopt($videoCurl, CURLOPT_HTTPHEADER, ['Content-Type: application/json' , $authorization]);

                $videoCurlResult = curl_exec($videoCurl);

                $videoJsonResponse = json_decode($videoCurlResult, true);

                if (! empty($videoJsonResponse['error'])) {
                    echo 'Error ' . $videoJsonResponse['error']['code'] . ': ' . $videoJsonResponse['error']['message'];

                    return $code;
                }

                $snippet = $videoJsonResponse['items'][0]['snippet'];
                $youtubeVideo = new YoutubeVideo(
                    $channelId,
                    $videoId,
                    'https://www.youtube.com/watch?v=' . $videoId,
                    $snippet['thumbnails']['high']['url'],
                    $snippet['title'],
                    $snippet['description'],
                    $snippet['tags'] ?? []
                );
                $videoRepository->addIfMissing($youtubeVideo);
                echo PHP_EOL . $youtubeVideo->getId() . ' inserted/updated !';
            }
        }

        echo PHP_EOL . PHP_EOL . 'Done !';

        return 0;
    }
}
