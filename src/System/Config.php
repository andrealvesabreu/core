<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

use League\Config\Configuration;
use Nette\Schema\Expect;

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
     * Get data from configuration
     *
     * @param string $item
     */
    public static function get(string $item = null)
    {
        try {
            return self::getConfig()->get($item);
        } catch (\Exception $e) {}
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
                self::addSchema($data['type'])->merge([
                    $data['type'] => $data
                ]);
                $count ++;
                break;
            } else {
                if (! isset($imp['type'])) {
                    echo "arquivo cagado\n";
                }
                self::addSchema($imp['type'])->merge([
                    $imp['type'] => [
                        $idx => $imp
                    ]
                ]);
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
            print_r(glob("{$path}/*.php"));
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

    private static function addSchema(string $type)
    {
        switch ($type) {
            case 'jwt':
                self::getConfig()->addSchema('jwt', Expect::structure([
                    'type' => Expect::string()->required()
                        ->default('jwt'),
                    'aud' => Expect::string()->required(),
                    'cty' => Expect::string()->required(),
                    'exp' => Expect::int()->min(1)
                        ->max(65535)
                        ->required(),
                    'iss' => Expect::string()->nullable(),
                    'nbf' => Expect::int()->nullable(),
                    'pass' => Expect::string()->required()
                ]));
                break;
            case 'cache':
                self::getConfig()->addSchema('cache', Expect::array([
                    Expect::structure([
                        'type' => Expect::string()->required()
                            ->default('cache'),
                        'driver' => Expect::anyOf('redis', 'memcached')->required(),
                        'host' => Expect::string()->default('localhost'),
                        'port' => Expect::int()->min(1)
                            ->max(65535),
                        'pass' => Expect::string()->nullable(),
                        'database' => Expect::int()->min(1)
                            ->max(16)
                            ->nullable(),
                        'ttl' => Expect::int()->min(30)
                            ->max(604800)
                            ->nullable()
                    ])
                ]));
                break;
            case 'log':
                self::getConfig()->addSchema('log', Expect::array([
                    Expect::structure([
                        'type' => Expect::string()->required()
                            ->default('log'),
                        'path' => Expect::string()->required(),
                        'name' => Expect::string()->required(),
                        'format' => Expect::string()->required(),
                        'date_format' => Expect::string()->required(),
                        'expire' => Expect::int()->min(1)
                            ->max(30)
                            ->required()
                    ])
                ]));
                break;
            case 's3':
                self::$config->addSchema('s3', Expect::array([
                    Expect::structure([
                        'type' => Expect::string()->required()
                            ->default('s3'),
                        'credentials' => Expect::structure([
                            'key' => Expect::string()->required(),
                            'secret' => Expect::string()->required()
                        ]),
                        'region' => Expect::string()->required(),
                        'version' => Expect::string()->nullable()
                    ])
                ]));
                break;
            case 'ftp':
                self::$config->addSchema('ftp', Expect::array([
                    Expect::structure([
                        'type' => Expect::string()->required()
                            ->default('ftp'),
                        'host' => Expect::string()->required(),
                        'root' => Expect::string()->required()
                            ->default('/'),
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
                        'ignorePassiveAddress' => Expect::bool()->nullable()
                            ->default(true),
                        'timestampsOnUnixListingsEnabled' => Expect::bool()->default(false),
                        'recurseManually' => Expect::bool()->default(false)
                    ])
                ]));
                break;
            case 'sftp':
                self::getConfig()->addSchema('sftp', Expect::array([
                    Expect::structure([
                        'type' => Expect::string()->required()
                            ->default('sftp'),
                        'host' => Expect::string()->required(),
                        'root' => Expect::string()->required()
                            ->default('/'),
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
                    ])
                ]));
                break;
            case 'queue':
                self::getConfig()->addSchema('queue', Expect::array([
                    Expect::anyOf([
                        Expect::structure([
                            'type' => Expect::string()->required()
                                ->default('queue'),
                            'driver' => Expect::string()->default('redis')
                                ->required(),
                            'host' => Expect::string()->default('localhost'),
                            'port' => Expect::int()->min(1)
                                ->max(65535)
                                ->default(6379),
                            'pass' => Expect::string()->nullable(),
                            'database' => Expect::int()->min(1)
                                ->max(16)
                                ->nullable(),
                            'ttl' => Expect::int()->min(30)
                                ->max(604800)
                                ->nullable(),
                            'producer' => Expect::string()->nullable(),
                            'processor' => Expect::string()->nullable()
                        ]),

                        Expect::structure([
                            'type' => Expect::string()->required()
                                ->default('cache'),
                            'driver' => Expect::string()->default('rabbit')
                                ->required(),
                            'host' => Expect::string()->default('localhost'),
                            'vhost' => Expect::string()->default('localhost'),
                            'port' => Expect::int()->min(1)
                                ->max(65535)
                                ->default(6379),
                            'pass' => Expect::string()->nullable(),
                            'persisted' => Expect::bool()->default(true)
                                ->required(),
                            'dsn' => Expect::string()->required(),
                            'ssl_cacert' => Expect::string()->nullable(),
                            'ssl_cert' => Expect::string()->nullable(),
                            'ssl_key' => Expect::string()->nullable(),
                            'producer' => Expect::string()->nullable(),
                            'processor' => Expect::string()->nullable()
                        ])
                    ])
                ]));
                break;

            case 'database':
                self::getConfig()->addSchema('database', Expect::array([
                    Expect::anyOf([
                        Expect::structure([
                            'type' => Expect::string()->required()
                                ->default('database'),
                            'driver' => Expect::string()->default('sqlsrv')
                                ->required(),
                            'host' => Expect::string()->default('localhost'),
                            'port' => Expect::int()->min(1)
                                ->max(65535)
                                ->default(1433),
                            'database' => Expect::string()->required(),
                            'username' => Expect::string()->nullable(),
                            'password' => Expect::string()->nullable()
                        ]),
                        Expect::structure([
                            'type' => Expect::string()->required()
                                ->default('database'),
                            'driver' => Expect::string()->default('mysql')
                                ->required(),
                            'host' => Expect::string()->default('localhost'),
                            'port' => Expect::int()->min(1)
                                ->max(65535)
                                ->default(3306),
                            'database' => Expect::string()->required(),
                            'username' => Expect::string()->nullable(),
                            'password' => Expect::string()->nullable(),
                            'charset' => Expect::string()->nullable(),
                            'collation' => Expect::string()->nullable(),
                            'prefix' => Expect::string()->nullable()
                        ]),
                        Expect::structure([
                            'type' => Expect::string()->required()
                                ->default('database'),
                            'driver' => Expect::string()->default('pgsql')
                                ->required(),
                            'host' => Expect::string()->default('localhost'),
                            'port' => Expect::int()->min(1)
                                ->max(65535)
                                ->default(5432),
                            'database' => Expect::string()->required(),
                            'username' => Expect::string()->nullable(),
                            'password' => Expect::string()->nullable(),
                            'charset' => Expect::string()->nullable(),
                            'prefix' => Expect::string()->nullable(),
                            'schema' => Expect::string()->nullable()
                        ])
                    ])
                ]));
                break;
        }
        return self::$config;
    }
}

