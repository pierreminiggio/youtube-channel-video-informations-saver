<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube\YoutubeVideo;

class YoutubeVideoRepository
{

    public function __construct(
        private DatabaseFetcher $fetcher,
    )
    {}

    public function addIfMissing(YoutubeVideo $video): void
    {

        $channelYoutubeId = $video->getChannel();
        
        $selectChannelQuery = [
            $this->fetcher->createQuery('youtube_channel')->select('id')->where('youtube_id = :id'),
            ['id' => $channelYoutubeId]
        ];
        $queriedChannels = $this->fetcher->query(...$selectChannelQuery);
        
        if (! $queriedChannels) {
            $this->fetcher->exec(
                $this->fetcher->createQuery('youtube_channel')->insertInto('youtube_id', ':id'),
                ['id' => $channelYoutubeId]
            );
        } else {
            // Update channel infos if needed
        }

        $queriedChannels = $this->fetcher->query(...$selectChannelQuery);
        $channelId = (int) $queriedChannels[0]['id'];

        $videoYoutubeId = $video->getId();
        
        $selectVideoQuery = [
            $this->fetcher->createQuery('youtube_video')->select('id')->where('youtube_id = :id'),
            ['id' => $videoYoutubeId]
        ];
        $queriedVideos = $this->fetcher->query(...$selectVideoQuery);

        $insertOrUpdateParams = [
            'channel_id' => $channelId,
            'id' => $videoYoutubeId,
            'url' => $video->getUrl(),
            'thumbnail' => $video->getThumbnail(),
            'title' => $video->getTitle(),
            'sanitized_title' => $video->getSanitizedTitle(),
            'description' => $video->getDescription(),
            'tags' => json_encode($video->getTags())
        ];
        
        if (! $queriedVideos) {
            $this->fetcher->exec(
                $this->fetcher
                    ->createQuery('youtube_video')
                    ->insertInto(
                        'channel_id,youtube_id,url,thumbnail,title,sanitized_title,description,tags',
                        ':channel_id,:id,:url,:thumbnail,:title,:sanitized_title,:description,:tags'
                    )
                ,
                $insertOrUpdateParams
            );
        } else {
            $this->fetcher->exec(
                $this->fetcher
                    ->createQuery('youtube_video')
                    ->update('
                        channel_id = :channel_id,
                        url = :url,
                        thumbnail = :thumbnail,
                        title = :title,
                        sanitized_title = :sanitized_title,
                        description = :description,
                        tags = :tags'
                    )
                    ->where('youtube_id = :id')
                ,
                $insertOrUpdateParams
            );
        }
    }
}
