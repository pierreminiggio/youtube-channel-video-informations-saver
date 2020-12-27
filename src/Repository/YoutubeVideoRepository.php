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
        } else {
            // Update channel infos if needed
        }

        $this->connection->stop();
        $this->connection->start();

        $queriedChannels = $this->connection->query(...$selectChannelQuery);
        $channelId = (int) $queriedChannels[0]['id'];

        $videoYoutubeId = $video->getId();
        $selectVideoQuery = ['SELECT id FROM youtube_video WHERE youtube_id = :id', ['id' => $videoYoutubeId]];
        $queriedVideos = $this->connection->query(...$selectVideoQuery);

        $insertOrUpdateParams = [
            'channel_id' => $channelId,
            'id' => $videoYoutubeId,
            'url' => $video->getUrl(),
            'thumbnail' => $video->getThumbnail(),
            'title' => $video->getTitle(),
            'sanitized_title' => $video->getSanitizedTitle(),
            'description' => $video->getDescription()
        ];
        
        if (! $queriedVideos) {
            $this->connection->exec('INSERT INTO youtube_video (
                channel_id,
                youtube_id,
                url,
                thumbnail,
                title,
                sanitized_title,
                description
            ) VALUES (
                :channel_id,
                :id,
                :url,
                :thumbnail,
                :title,
                :sanitized_title,
                :description
            )', $insertOrUpdateParams);
        } else {
            $this->connection->exec('UPDATE youtube_video SET
                channel_id = :channel_id,
                url = :url,
                thumbnail = :thumbnail,
                title = :title,
                sanitized_title = :sanitized_title,
                description = :description
            WHERE youtube_id = :id
            ', $insertOrUpdateParams);
        }

        $this->connection->stop();
    }
}
