
<?php
// Copyright (c) 2022 André Alves <aluisalves0@gmail.com>
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

return [
    "type" => "filesystem",
    "config" => [
        [
            'name' => 'localfs',
            "adapter" => "local",
            'root' => dirname(__DIR__) . '/logs'
        ],
        [
            'name' => 'ftpsystem',
            'adapter' => 'ftp',
            'root' => '/', // required
            'host' => 'ftp.dlptest.com', // required
            'username' => 'dlpuser', // required
            'password' => 'rNrKYTX9g7z3RgJRmxWuGHbeu', // required
            'port' => 21,
            // 'ssl' => false,
            // 'timeout' => 90,
            // 'utf8' => false,
            // 'passive' => true,
            // 'transferMode' => FTP_BINARY,
            // 'systemType' => null, // 'windows' or 'unix'
            // 'ignorePassiveAddress' => null, // true or false
            // 'timestampsOnUnixListingsEnabled' => false, // true or false
            // 'recurseManually' => true // true            
        ],
        [
            'name' => 'sftpsystem',
            'adapter' => 'sftp',
            'root' => '/root/dir', // required
            'host' => 'host.com',
            'username' => 'user', // required
            'password' => 'pass', // required
            'pkey' => null,
            'passkey' => null,
            'port' => 22,
            'use_agent' => false,
            'timeout' => null,
            'max_tries' => null,
            'fingerprint' => null,
            'mod' => [
                'file' => [
                    'public' => 0640,
                    'private' => 0604
                ],
                'dir' => [
                    'public' => 0740,
                    'private' => 0604
                ]
            ]
        ],
        [
            'name' => 'aws',
            'adapter' => 's3',
            'debug' => false,
            'version' => 'latest',
            'region' => 'region',
            'credentials' => [
                'key' => 'awskey',
                'secret' => 'awssecret'
            ],
            'bucket' => 'yourbucket',
            'prefix' => '',
            'visibility' => 'public'
        ]
    ]
];
