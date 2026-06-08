<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\AI\Platform\Bridge\MiniMax\Factory;
use Symfony\AI\Platform\Message\Content\Text;

require_once dirname(__DIR__).'/bootstrap.php';

$platform = Factory::createPlatform(env('MINI_MAX_API_KEY'), http_client());

// Video generation is asynchronous; the bridge polls the task until the file is ready.
$result = $platform->invoke('MiniMax-Hailuo-02', new Text('A cat playing the piano on a stage, cinematic lighting'), [
    'duration' => 6,
    'resolution' => '768P',
]);

file_put_contents(__DIR__.'/minimax-video.mp4', $result->asBinary());

echo 'Video written to minimax-video.mp4'.\PHP_EOL;
