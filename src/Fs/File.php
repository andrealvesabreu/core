<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

/**
 * @method static string get(string $path)
 * @method static string put(string $path, string $contents)
 * @method static string set(string $path, string $contents)
 * @method static string chdir(string $path)
 * @method static string mkdir(string $path)
 * @method static string copy(string $source, string $destination)
 * @method static string move(string $source, string $destination)
 * @method static string delete(string $path)
 * @method static string deleteDirectory(string $path)
 * @method static string list(string $path)
 */
final class File
{

    /**
     * List of file manipulator already initialized
     */
    private static $handlers = [];

    /**
     * Get a specific handler to file manipulation
     *
     * @param string $handlerName
     * @throws \Exception
     * 
     * @return mixed
     */
    public static function on(string $handlerName): ?BaseFs
    {
        if (!isset(self::$handlers[$handlerName])) {
            $first = empty(self::$handlers);
            self::$handlers[$handlerName] = FileFactory::create($handlerName);
            /**
             * If this is first handler, allow to use it as default
             */
            if ($first) {
                self::$handlers['default'] = &self::$handlers[$handlerName];
            }
            return self::$handlers[$handlerName];
        } else {
            return self::$handlers[$handlerName];
        }
        throw new \Exception("Invalid filesystem configuration");
    }

    /**
     * Get a specific handler to file manipulation
     * 
     * @param string $handlerName
     * @param array $config
     * 
     * @return BaseFs|null
     */
    public static function with(string $handlerName, array $config): ?BaseFs
    {
        if (!isset(self::$handlers[$handlerName])) {
            $first = empty(self::$handlers);
            self::$handlers[$handlerName] = FileFactory::create($handlerName, $config);
            /**
             * If this is first handler, allow to use it as default
             */
            if ($first) {
                self::$handlers['default'] = self::$handlers[$handlerName];
            }
            return self::$handlers[$handlerName];
        } else {
            return self::$handlers[$handlerName];
        }
        throw new \Exception("Invalid filesystem configuration");
    }



    /**
     * Call statically 
     */
    public static function __callStatic($name, $arguments)
    {
        /**
         * Use default handler if calling statically a non static method
         * If default handler does not exists, create a local filesystem handler mapping from root local filesystem
         */
        if (!isset(self::$handlers['default'])) {
            self::$handlers['default'] = FileFactory::create('default', [
                'adapter' => 'local',
                'root' => '/'
            ]);
        }
        /**
         * Dispatch call through default filesystem handler
         */
        return call_user_func_array([self::$handlers['default'], $name], $arguments);
    }
}
