<?php
ini_set("display_errors", true);
error_reporting(E_ALL);

use Inspire\Core\Fs\File;

define('APP_NAME', 'test');
include dirname(__DIR__) . '/vendor/autoload.php';
echo Inspire\Config\Config::loadFromFile('config/filesystem.php') . PHP_EOL;

// localTest();
ftpTest();
ftpTestCurl();
// sftpTest();
// s3Test();

function localTest()
{
    echo "On named intance call\n";
    var_dump(File::on('localfs')->mkdir('test'));
    File::on('localfs')->put('local_test.txt', 'i wanna go');
    var_dump(File::on('localfs')->get('local_test.txt'));
    File::on('localfs')->copy('local_test.txt', 'localtest2.txt');
    File::on('localfs')->move('localtest2.txt', 'localtest2slme.txt');
    File::on('localfs')->delete('local_test.txt');
    var_dump(File::on('localfs')->get('localtest2slme.txt'));
    var_dump(File::on('localfs')->list());

    echo "\n\nWith manually confguration\n";
    var_dump(File::with('manual', [
        'adapter' => 'local',
        'root' => 'logs/'
    ])->set('local_test_with.txt', 'setting data to file statically'));
    var_dump(File::set('local_test_with.txt', 'Data will not be overwrited statically'));
    var_dump(File::get('local_test_with.txt'));
    var_dump(File::put('local_test_with.txt', 'replacing data statically'));
    var_dump(File::get('local_test_with.txt'));
}

function ftpTest()
{
    echo "\n\n\n\n\n\n\n\n\nFTP\n";
    var_dump(File::on('ftpsystem')->mkdir('test'));
    File::on('ftpsystem')->chdir('test');
    var_dump(File::on('ftpsystem')->set('ftp_test.txt', 'setting data to file statically'));
    File::on('ftpsystem')->copy('ftp_test.txt', 'ftp_test2.txt');
    File::on('ftpsystem')->move('ftp_test2.txt', 'ftp_test2_a.txt');
    File::on('ftpsystem')->delete('ftp_test.txt');
    var_dump(File::on('ftpsystem')->get('ftp_test2_a.txt'));
    var_dump(File::on('ftpsystem')->list());
    File::on('ftpsystem')->chdir('/');
    var_dump(File::on('ftpsystem')->list());
}
function ftpTestCurl()
{
    echo "\n\n\n\n\n\n\n\n\nCURL FTP\n";
    var_dump(File::on('ftpsystem')->mkdir('teste'));
    var_dump(File::on('ftpsystem')->mkdir('teste2'));
    var_dump(File::on('ftpsystem')->list());
    File::on('ftpsystem')->chdir('teste');
    var_dump(File::on('ftpsystem')->list());
    var_dump(File::on('ftpsystem')->set('ftp_test.txt', 'setting data to file statically not'));
    var_dump(File::on('ftpsystem')->put('ftp_test.txt', 'setting data to file statically again'));
    File::on('ftpsystem')->copy('ftp_test.txt', 'ftp_test2.txt');
    File::on('ftpsystem')->move('ftp_test2.txt', 'ftp_test2_a.txt');
    File::on('ftpsystem')->chdir('../teste2');
    var_dump(File::on('ftpsystem')->put('ftp_test2.txt', 'Gooooooooo'));
    File::on('ftpsystem')->chdir('../teste');
    var_dump(File::on('ftpsystem')->get('ftp_test2_a.txt'));
    File::on('ftpsystem')->delete('../teste2/ftp_test2.txt');
    File::on('ftpsystem')->deleteDirectory('../teste2');
    var_dump(File::on('ftpsystem')->set('ftp_test.txt', 'setting data to file statically'));
    var_dump(File::on('ftpsystem')->list());
    File::on('ftpsystem')->chdir('/');
    var_dump(File::on('ftpsystem')->list());
}

function sftpTest()
{
    echo "\n\n\n\n\n\n\n\n\nSFTP\n";
    var_dump(File::on('sftpsystem')->mkdir('test'));
    File::on('sftpsystem')->chdir('test');
    var_dump(File::on('sftpsystem')->set('ftp_test.txt', 'setting data to file statically'));
    File::on('sftpsystem')->copy('ftp_test.txt', 'ftp_test2.txt');
    File::on('sftpsystem')->delete('ftp_test2_a.txt');
    File::on('sftpsystem')->move('ftp_test2.txt', 'ftp_test2_a.txt');
    File::on('sftpsystem')->delete('ftp_test.txt');
    var_dump(File::on('sftpsystem')->get('ftp_test2_a.txt'));
    var_dump(File::on('sftpsystem')->list());
    File::on('sftpsystem')->chdir('/');
    var_dump(File::on('sftpsystem')->list());
}

function s3Test()
{
    echo "\n\n\n\n\n\n\n\n\nAWS S3\n";
    var_dump(File::on('aws')->mkdir('test'));
    var_dump(File::on('aws')->chdir('test'));
    var_dump(File::on('aws')->set('aws_test.txt', 'setting data to file statically'));
    File::on('aws')->copy('aws_test.txt', 'aws_test2.txt');
    File::on('aws')->move('aws_test2.txt', 'aws_test2_a.txt');
    File::on('aws')->delete('aws_test.txt');
    var_dump(File::on('aws')->get('aws_test2_a.txt'));
    var_dump(File::on('aws')->list());
    File::on('aws')->chdir('/');
    var_dump(File::on('aws')->list());
}
