<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class YoutubeChannelRepository
{

    public function __construct(
        private DatabaseConnection $connection,
    )
    {}

    /**
     * @return string[] youtube channel ids
     */
    public function findAll(): array
    {
        $this->connection->start();
        $entries = $this->connection->query('SELECT youtube_id FROM youtube_channel', []);
        $this->connection->stop();

        return array_map(fn ($entry) => $entry['youtube_id'], $entries);
    }
}
