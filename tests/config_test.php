<?php
use Inspire\Core\Logger\Log;
use Psr\Log\LogLevel;

define('APP_NAME', 'test');
include dirname(__DIR__) . '/vendor/autoload.php';
Inspire\Core\System\Config::loadFromFolder('config');
var_dump(Inspire\Core\System\Config::get('cache.cache4.driver'));
var_dump(Inspire\Core\System\Config::get('cache.cache7.host'));
var_dump(Inspire\Core\System\Config::get('cache.i18n'));
var_dump(Inspire\Core\System\Config::get('jwt.aud'));
var_dump(Inspire\Core\System\Config::get('log.warn.format'));
var_dump(Inspire\Core\System\Config::get('s3.sec.credentials.secret'));
var_dump(Inspire\Core\System\Config::get('sftp.sftp3.root'));
var_dump(Inspire\Core\System\Config::get('sftp.sftp3.mod.file.public'));
var_dump(Inspire\Core\System\Config::get('queue.teste.driver'));
var_dump(Inspire\Core\System\Config::get('queue.teste.database'));
var_dump(Inspire\Core\System\Config::get('queue.amqp.dsn'));
var_dump(Inspire\Core\System\Config::get('database.mysql.collation'));
var_dump(Inspire\Core\System\Config::get('database.sqlsrv.username'));
Log::info("Test info");
Log::error("Test error");
Log::warning("Test warning");


