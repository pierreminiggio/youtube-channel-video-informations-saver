<?php

namespace PierreMiniggio\YoutubeChannelVideoInformationsSaver\Connection;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class DatabaseConnectionFactory
{
    public function makeFromConfig($config): DatabaseConnection
    {
        
        return new DatabaseConnection(
            $config['host'],
            $config['database'],
            $config['username'],
            $config['password'],
        );
    }
}
