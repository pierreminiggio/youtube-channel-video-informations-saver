<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class YoutubeChannelRepository
{

    public function __construct(
        private DatabaseFetcher $fetcher,
    )
    {}

    /**
     * @return string[] youtube channel ids
     */
    public function findAll(): array
    {
        $entries = $this->fetcher->query(
            $this->fetcher->createQuery('youtube_channel')
                ->select('youtube_id')
        );

        return array_map(fn ($entry) => $entry['youtube_id'], $entries);
    }
}
