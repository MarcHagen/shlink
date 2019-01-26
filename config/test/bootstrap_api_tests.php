<?php
declare(strict_types=1);

namespace ShlinkioTest\Shlink\Common;

use Psr\Container\ContainerInterface;
use function file_exists;
use function touch;

// Create an empty .env file
if (! file_exists('.env')) {
    touch('.env');
}

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../container.php';
$container->get(TestHelper::class)->createTestDb();
ApiTest\ApiTestCase::setApiClient($container->get('shlink_test_api_client'));
