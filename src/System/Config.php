<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

use Psr\Log\LogLevel;
use Inspire\Core\Utils\Arrays;
use Inspire\Core\Utils\JsonValidator;

/**
 *
 * @author aalves
 *        
 */
class Config
{

    /**
     * Array to laod application config
     *
     * @var array
     */
    protected static ?array $config = [];

    /**
     * Get data from configuration
     *
     * @param string $item
     */
    public static function get(string $item = null)
    {
        try {
            return Arrays::get(self::$config, $item);
        } catch (\Exception $e) {
            // echo ($e->getMessage() . "!!");
            return null;
        }
    }

    /**
     * Add data from input array to configuration
     *
     * @param array $data
     * @return int
     */
    public static function addConfig(array $data, string $type): int
    {
        $count = 0;
        foreach ($data as $imp) {
            if (! isset($imp['name'])) {
                echo "You must provide a identifier name for every configuration!\n";
                continue;
            }
            Arrays::set(self::$config, "{$type}.{$imp['name']}", $imp);
            $count ++;
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
                $data = self::getFromFile($file);
                $count += self::addConfig($data['config'], $data['type']);
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
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $data = self::getFromFile($path);
        return self::addConfig($data['config'], $data['type']);
    }

    /**
     * Load array configuration from file
     *
     * @param string $path
     * @param bool $check
     * @throws \Exception
     * @return array|NULL
     */
    private static function getFromFile(string $path, bool $check = false): ?array
    {
        if (! $check || (file_exists($path) && is_file($path) && is_readable($path))) {
            $data = require $path;
            if (! isset($data['type'])) {
                throw new \Exception("Could not determine type of " . basename($path) . " configuration. Missing 'type' field!");
            } else if (! isset($data['config'])) {
                throw new \Exception("Could not find " . basename($path) . " configuration detals. Missing section 'config'!");
            } else {
                return $data;
            }
        }
        return null;
    }

    /**
     * Check configuration in $config var based on JSON schema
     * If $config is not filled, try to validate all configuration files
     *
     * @param array $config
     * @return bool
     */
    public static function checkConfiguration(array $config): bool
    {
        try {
            $schema = dirname(dirname(__DIR__)) . "/schemas/{$config['type']}.json";
            // echo json_encode($config, JSON_UNESCAPED_UNICODE);
            if (! JsonValidator::validateJson(json_encode($config, JSON_UNESCAPED_UNICODE), $schema)) {
                print_r(JsonValidator::getReadableErrors());
                return false;
            }
            return true;
        } catch (\Exception $e) {
            echo "An error occurred trying to validate configuration: {$e->getTraceAsString()}";
        }
    }

    public static function checkConfigurationFolder(string $path): bool
    {
        try {
            $ok = true;
            $path = rtrim($path, DIRECTORY_SEPARATOR);
            if (file_exists($path) && is_dir($path) && is_readable($path)) {
                foreach (glob("{$path}/*.php") as $file) {
                    $data = self::getFromFile($file);
                    $ok = $ok && self::checkConfiguration($data);
                }
            }
        } catch (\Exception $e) {
            echo "An error occurred trying to validate configuration: {$e->getTraceAsString()}";
        }
        return $ok;
    }
}

