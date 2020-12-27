<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class YoutubeVideoRepository
{

    public function __construct(private DatabaseConnection $connection)
    {}
}
