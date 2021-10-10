<?php
define('APP_NAME', 'horse');
include '/home/aalves/eclipse-workspace/core/vendor/autoload.php';
Inspire\Core\System\Config::loadFromFolder('/home/aalves/eclipse-workspace/temp/');
var_dump(Inspire\Core\System\Config::get('cache.salame.driver'));
var_dump(Inspire\Core\System\Config::get('cache.salame.host'));
var_dump(Inspire\Core\System\Config::get('cache.i18n'));
var_dump(Inspire\Core\System\Config::get('jwt.aud'));
var_dump(Inspire\Core\System\Config::get('log.warn.format'));
var_dump(Inspire\Core\System\Config::get('s3.sec.credentials.secret'));
var_dump(Inspire\Core\System\Config::get('sftp.sftp3.root'));
var_dump(Inspire\Core\System\Config::get('sftp.sftp3.mod.file.public'));
var_dump(Inspire\Core\System\Config::get('queue.teste.driver'));
var_dump(Inspire\Core\System\Config::get('queue.teste.database'));
var_dump(Inspire\Core\System\Config::get('queue.amqp.dsn'));

