<?php

require __DIR__ . '/vendor/autoload.php';

use PierreMiniggio\YoutubeChannelVideoInformationsSaver\App;

try {
    exit((new App())->run());
} catch (Throwable $e) {
    echo get_class($e) . ' : ' . $e->getMessage();
    exit;
}
