<?php
declare(strict_types = 1);
namespace Inspire\Core\Cache;

use Inspire\Core\Factories\CacheFactory;

/**
 * Description of Cache
 *
 * @author aalves
 */
abstract class Cache
{

    /**
     * Collection of Cache objects
     *
     * @var array
     */
    public static array $cache = [];

    /**
     * Call private function statically.It will work only for default cache
     *
     * @param string $method
     * @param array $arguments
     */
    public static function __callstatic(string $method, array $arguments)
    {
        /**
         * If cache default isn't initialized yet
         */
        if (! isset(self::$cache['default'])) {

            self::$cache['default'] = new CachePool('default');
        }
        call_user_func_array([
            self::$cache['default'],
            $method
        ], $arguments);
        /**
         * Return Log object
         */
        return self::$cache['default'];
    }

    /**
     * Set a channel before call its methods
     *
     * @param string $channel
     */
    public static function on(string $channel)
    {
        /**
         * If channel default isn't initialized yet
         */
        if (! isset(self::$cache[$channel])) {
            self::$cache[$channel] = CacheFactory::create($channel);
        }
        /**
         * Return Cache object
         */
        return self::$cache[$channel];
    }
}

