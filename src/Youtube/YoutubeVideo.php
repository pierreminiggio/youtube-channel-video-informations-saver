<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Youtube;

class YoutubeVideo
{

    public function __construct(
        private string $channel,
        private string $id,
        private string $url,
        private string $thumbnail,
        private string $title,
        private string $description,
        private array $tags
    )
    {}

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getSanitizedTitle(): string
    {
        return str_replace('.', '', mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $this->title));
    }
}
