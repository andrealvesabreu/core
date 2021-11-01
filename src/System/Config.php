<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

use League\Config\Configuration;
use Nette\Schema\ {
    Expect,
    Processor
};
use Psr\Log\LogLevel;

/**
 *
 * @author aalves
 *        
 */
class Config
{

    /**
     * Inscatence of \League\Config\Configuration
     *
     * @var Configuration
     */
    protected static ?Configuration $config = null;

    /**
     *
     * @var Processor
     */
    private static ?Processor $processor = null;

    /**
     * Get data from configuration
     *
     * @param string $item
     */
    public static function get(string $item = null)
    {
        try {
            return self::getConfig()->get($item);
        } catch (\Exception $e) {
            // echo ($e->getMessage() . "!!");
            return null;
        }
    }

    /**
     * Add data from input array to configuration
     *
     * @param string $group
     * @param string $item
     */
    public static function addConfig(array $data)
    {
        $count = 0;
        foreach ($data as $idx => $imp) {
            if (! is_array($imp)) {
                if (! isset($data['type'])) {
                    echo "arquivo cagado\n";
                }
                self::addSchema($data);
                $count ++;
                break;
            } else {
                if (! isset($imp['type'])) {
                    echo "arquivo cagado\n";
                }
                self::addSchema($imp, $idx);
                $count ++;
            }
        }
        return $count;
    }

    /**
     * Load all configuration files from specified folder
     * The basename of each file will be the name of the group
     *
     * @param string $path
     * @return int
     */
    public static function loadFromFolder(string $path): int
    {
        $count = 0;
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        if (file_exists($path) && is_dir($path) && is_readable($path)) {
            foreach (glob("{$path}/*.php") as $file) {
                $data = require $file;
                $count += self::addConfig($data);
            }
        }
        return $count;
    }

    /**
     *
     * Load configurations from specified file
     * The basename of this file will be the name of the group
     *
     * @param string $path
     * @return int
     */
    public static function loadFromFile(string $path): int
    {
        $count = 0;
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        if (file_exists($path) && is_file($path) && is_readable($path)) {
            $data = require $path;
            $count += self::addConfig($data);
        }
        return $count;
    }

    /**
     * Get configuration object
     *
     * @return \League\Config\Configuration
     */
    private static function getConfig(): Configuration
    {
        if (self::$config === null) {
            self::$config = new Configuration();
        }
        return self::$config;
    }

    /**
     *
     * @param array $data
     * @param string $name
     * @return Configuration|NULL
     */
    private static function addSchema(array $data, string $name = null): ?Configuration
    {
        switch ($data['type']) {
            case 'jwt':
                $schema = Expect::structure([
                    'type' => Expect::string()->required()->default('jwt'),
                    'aud' => Expect::string()->required(),
                    'cty' => Expect::string()->required(),
                    'exp' => Expect::int()->min(1)
                        ->max(65535)
                        ->required(),
                    'iss' => Expect::string()->nullable(),
                    'nbf' => Expect::int()->nullable(),
                    'pass' => Expect::string()->required()
                ]);
                self::getConfig()->addSchema('jwt', $schema);
                break;
            case 's3':
                $schema = Expect::structure([
                    'type' => Expect::string()->required()->default('s3'),
                    'credentials' => Expect::structure([
                        'key' => Expect::string()->required(),
                        'secret' => Expect::string()->required()
                    ]),
                    'region' => Expect::string()->required(),
                    'version' => Expect::string()->nullable()
                ]);
                self::getConfig()->addSchema('s3', Expect::array([
                    $schema
                ]));
                break;
            case 'cache':
                $schema = Expect::structure([
                    'type' => Expect::string()->required()->default('cache'),
                    'driver' => Expect::anyOf('redis', 'memcached')->required(),
                    'host' => Expect::string()->default('localhost'),
                    'port' => Expect::int()->min(1)->max(65535),
                    'user' => Expect::string()->nullable(),
                    'pass' => Expect::string()->nullable(),
                    'database' => Expect::int()->min(1)
                        ->max(16)
                        ->nullable(),
                    'ttl' => Expect::int()->min(30)
                        ->max(604800)
                        ->nullable()
                ]);
                self::getConfig()->addSchema('cache', Expect::array([
                    $schema
                ]));
                break;
            case 'log':
                $schema = Expect::structure([
                    'type' => Expect::string('log')->required(),
                    'level' => Expect::anyOf(LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::DEBUG, LogLevel::EMERGENCY, LogLevel::ERROR, LogLevel::INFO, LogLevel::NOTICE, LogLevel::WARNING)->required(),
                    'channel' => Expect::string()->nullable(),
                    'filename' => Expect::string()->required(),
                    'format' => Expect::string()->nullable(),
                    'date_format' => Expect::string()->required(),
                    'max_files' => Expect::int()->min(1)
                        ->max(30)
                        ->required(),
                    'file_perms' => Expect::int()->min(0)
                        ->max(777)
                        ->required()
                ]);
                self::getConfig()->addSchema('log', Expect::array([
                    $schema
                ]));
                break;
            case 'ftp':
                $schema = Expect::structure([
                    'type' => Expect::string()->required()->default('ftp'),
                    'host' => Expect::string()->required(),
                    'root' => Expect::string()->required()->default('/'),
                    'username' => Expect::string()->required(),
                    'password' => Expect::string()->required(),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(21),
                    'ssl' => Expect::bool()->default(false),
                    'timeout' => Expect::int()->min(10)
                        ->max(300)
                        ->default(30),
                    'utf8' => Expect::bool()->default(false),
                    'passive' => Expect::bool()->default(true),
                    'transferMode' => Expect::arrayOf([
                        1,
                        2
                    ])->nullable(),
                    'systemType' => Expect::arrayOf([
                        'windows',
                        'unix'
                    ])->nullable(),
                    'ignorePassiveAddress' => Expect::bool()->nullable()->default(true),
                    'timestampsOnUnixListingsEnabled' => Expect::bool()->default(false),
                    'recurseManually' => Expect::bool()->default(false)
                ]);
                self::getConfig()->addSchema('ftp', Expect::array([
                    $schema
                ]));
                break;
            case 'sftp':
                $schema = Expect::structure([
                    'type' => Expect::string()->required()->default('sftp'),
                    'host' => Expect::string()->required(),
                    'root' => Expect::string()->required()->default('/'),
                    'username' => Expect::string()->required(),
                    'password' => Expect::string()->required(),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(21),
                    'timeout' => Expect::int()->min(10)
                        ->max(300)
                        ->default(30),
                    'pkey' => Expect::string()->nullable(),
                    'passkey' => Expect::string()->nullable(),
                    'max_tries' => Expect::int()->min(1)
                        ->max(15)
                        ->nullable()
                        ->default(4),
                    'fingerprint' => Expect::string()->nullable(),
                    'systemType' => Expect::anyOf([
                        'windows',
                        'unix'
                    ])->nullable(),
                    'mod' => Expect::structure([
                        'file' => Expect::structure([
                            'public' => Expect::string()->required(),
                            'private' => Expect::string()->required()
                        ]),
                        'dir' => Expect::structure([
                            'public' => Expect::string()->required(),
                            'private' => Expect::string()->required()
                        ])
                    ])->required()
                ]);
                self::getConfig()->addSchema('sftp', Expect::array([
                    $schema
                ]));
                break;
            case 'queue':
                $schema = Expect::anyOf(Expect::structure([
                    'type' => Expect::string()->required()->default('queue'),
                    'driver' => Expect::anyOf('rabbit')->required(),
                    'host' => Expect::string()->default('localhost')->required(),
                    'vhost' => Expect::string('/')->default('localhost'),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(5672),
                    'user' => Expect::string('guest')->nullable(),
                    'pass' => Expect::string('guest')->nullable(),
                    'read_timeout' => Expect::int()->min(1)
                        ->max(30)
                        ->default(3),
                    'write_timeout' => Expect::int()->min(1)
                        ->max(30)
                        ->default(3),
                    'connection_timeout' => Expect::int()->min(1)
                        ->max(30)
                        ->default(3),
                    'heartbeat' => Expect::int()->min(1)
                        ->max(30)
                        ->default(0),
                    'persisted' => Expect::bool()->default(true)->required(),
                    'lazy' => Expect::bool()->default(true),
                    'qos_global' => Expect::bool()->default(false),
                    'qos_prefetch_size' => Expect::int()->min(1)
                        ->max(30)
                        ->default(0),
                    'qos_prefetch_count' => Expect::int()->min(1)
                        ->max(30)
                        ->default(1),
                    'exchange' => Expect::string()->required(),
                    'queue_type' => Expect::anyOf('direct', 'fanout', 'headers', 'topic')->required(),
                    'ssl_on' => Expect::bool()->default(false),
                    'ssl_verify' => Expect::bool()->default(true),
                    'ssl_cacert' => Expect::string()->nullable(),
                    'ssl_cert' => Expect::string()->nullable(),
                    'ssl_key' => Expect::string()->nullable(),
                    'ssl_passphrase' => Expect::string()->nullable(),
                    'processor' => Expect::string()->required()
                ]), Expect::structure([
                    'type' => Expect::string()->required()->default('queue'),
                    'driver' => Expect::anyOf('redis')->required(),
                    'host' => Expect::string()->default('localhost'),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(6379),
                    'user' => Expect::string()->nullable(),
                    'pass' => Expect::string()->nullable(),
                    'persisted' => Expect::bool()->default(true)->required(),
                    'database' => Expect::int()->min(1)
                        ->max(16)
                        ->required(),
                    'read_timeout' => Expect::int()->min(1)
                        ->max(30)
                        ->default(3),
                    'connection_timeout' => Expect::int()->min(1)
                        ->max(30)
                        ->default(3),
                    'processor' => Expect::string()->required()
                ]));
                self::getConfig()->addSchema('queue', Expect::array([
                    $schema
                ]));
                break;
            case 'database':
                $schema = Expect::anyOf(Expect::structure([
                    'type' => Expect::string()->required()->default('database'),
                    'driver' => Expect::anyOf('sqlsrv')->required(),
                    'host' => Expect::string()->default('localhost')->required(),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(1433),
                    'database' => Expect::string()->required(),
                    'username' => Expect::string()->nullable(),
                    'password' => Expect::string()->nullable()
                ]), Expect::structure([
                    'type' => Expect::string('database')->required(),
                    'driver' => Expect::anyOf('mysql')->required(),
                    'host' => Expect::string()->default('localhost')->required(),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(3306),
                    'database' => Expect::string()->required(),
                    'username' => Expect::string()->nullable(),
                    'password' => Expect::string()->nullable(),
                    'charset' => Expect::string()->nullable(),
                    'collation' => Expect::string()->nullable(),
                    'prefix' => Expect::string()->nullable()
                ]), Expect::structure([
                    'type' => Expect::string()->required()->default('database'),
                    'driver' => Expect::anyOf('pgsql')->required(),
                    'host' => Expect::string()->default('localhost')->required(),
                    'port' => Expect::int()->min(1)
                        ->max(65535)
                        ->default(5432),
                    'database' => Expect::string()->required(),
                    'username' => Expect::string()->nullable(),
                    'password' => Expect::string()->nullable(),
                    'charset' => Expect::string()->nullable(),
                    'prefix' => Expect::string()->nullable(),
                    'schema' => Expect::string()->nullable()
                ]));
                self::getConfig()->addSchema('database', Expect::array([
                    $schema
                ]));
                break;
        }
        if (self::$processor === null) {
            self::$processor = new \Nette\Schema\Processor();
        }
        try {
            if ($name === null) {
                $addData = [
                    $data['type'] => $data
                ];
            } else {
                $addData = [
                    $data['type'] => [
                        $name => $data
                    ]
                ];
            }
            self::$processor->process($schema, $data);
            self::getConfig()->merge($addData);
        } catch (\Nette\Schema\ValidationException $e) {
            echo 'Data is invalid: ' . $e->getMessage();
        }
        return self::$config;
    }
}

