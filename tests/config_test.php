<?php
use Inspire\Core\Logger\Log;
use Psr\Log\LogLevel;
use Inspire\Core\Factories\FactoryLogger;
use Inspire\Core\Cache\Cache;

define('APP_NAME', 'test');
include dirname(__DIR__) . '/vendor/autoload.php';
// Load all files from foler
echo Inspire\Core\System\Config::loadFromFolder('config') . PHP_EOL;
// print_r(Inspire\Core\System\Config::get());
// Load a single file
echo Inspire\Core\System\Config::loadFromFile('config/s3.php') . PHP_EOL;
// print_r(Inspire\Core\System\Config::get());
/**
 * Validate an array configuration
 */
Inspire\Core\System\Config::checkConfiguration([
    "type" => "s3",
    "config" => [
        [
            'name' => 'first',
            'credentials' => [
                'key' => 'your-keyyour-keyyour-keyyour-key',
                'secret' => 'your-secretyour-secretyour-secretyour-secretyour-secretyour-secretyour-secret'
            ],
            'region' => 'us-east-1',
            'version' => 'latest'
        ],
        [
            'name' => 'second',
            'credentials' => [
                'key' => 'your-keyyour-keyyour-keyyour-keyyour',
                'secret' => 'your-secretyour-secretyour-secretyour-secretyour-secretyour-secretyour-secret'
            ],
            'region' => 'sa-east-1',
            'version' => 'latest'
        ]
    ]
]);
/**
 * Validate all file in folder configuration
 */
Inspire\Core\System\Config::checkConfigurationFolder('config');
var_dump(Inspire\Core\System\Config::get('cache.cache4.driver'));
var_dump(Inspire\Core\System\Config::get('cache.i18n.host'));
var_dump(Inspire\Core\System\Config::get('cache.i18n'));
var_dump(Inspire\Core\System\Config::get('jwt.first.exp'));
var_dump(Inspire\Core\System\Config::get('log.warnapp.filename'));
var_dump(Inspire\Core\System\Config::get('s3.second.credentials.secret'));
var_dump(Inspire\Core\System\Config::get('filesystem.sftp3.root'));
var_dump(Inspire\Core\System\Config::get('filesystem.sftp3.mod.file.public'));
var_dump(Inspire\Core\System\Config::get('queue.track.driver'));
var_dump(Inspire\Core\System\Config::get('queue.track.vhost'));
var_dump(Inspire\Core\System\Config::get('database.mysqltest.collation'));
var_dump(Inspire\Core\System\Config::get('database.pgtestconfig.user'));
// FactoryLogger::create('info', APP_NAME);
Log::info("Test info");
Log::on(APP_NAME)->info("Test info channel " . APP_NAME);
Log::on(APP_NAME)->info('Test', 'multi', 'info', 'channel', APP_NAME)->error('multi', 'error', 'too');
// Log::warning("Test warning");

Cache::on('cache6')->set('deco', '123456', 60);
echo Cache::on('cache6')->get('deco') . PHP_EOL;

