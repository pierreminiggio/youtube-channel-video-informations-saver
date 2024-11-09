<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube;

use PierreMiniggio\YoutubeAPI\YoutubeVideo as YoutubeAPIYoutubeVideo;

class YoutubeVideo extends YoutubeAPIYoutubeVideo
{
    public static function makeFromYoutubeAPIYoutubeVideo(YoutubeAPIYoutubeVideo $youtubeAPIYoutubeVideo): self
    {
        return new self(
            $youtubeAPIYoutubeVideo->getChannel(),
            $youtubeAPIYoutubeVideo->getId(),
            $youtubeAPIYoutubeVideo->getUrl(),
            $youtubeAPIYoutubeVideo->getThumbnail(),
            $youtubeAPIYoutubeVideo->getTitle(),
            $youtubeAPIYoutubeVideo->getDescription(),
            $youtubeAPIYoutubeVideo->getTags()
        );
    }

    public function getSanitizedTitle(): string
    {
        return str_replace('.', '', mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $this->title));
    }
}
