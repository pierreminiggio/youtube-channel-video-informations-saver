<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube\YoutubeVideo;

class YoutubeVideoRepository
{

    public function __construct(
        private DatabaseConnection $connection,
    )
    {}

    public function addIfMissing(YoutubeVideo $video): void
    {
        $this->connection->start();

        $channelYoutubeId = $video->getChannel();
        $selectChannelQuery = ['SELECT id FROM youtube_channel WHERE youtube_id = :id', ['id' => $channelYoutubeId]];
        $queriedChannels = $this->connection->query(...$selectChannelQuery);
        
        if (! $queriedChannels) {
            $this->connection->exec('INSERT INTO youtube_channel (youtube_id) VALUES (:id)', ['id' => $channelYoutubeId]);
        }

        $queriedChannels = $this->connection->query(...$selectChannelQuery);

        $channelId = (int) $queriedChannels[0]['id'];

        var_dump($channelId);
    }
}
