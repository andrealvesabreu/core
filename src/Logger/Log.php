<?php
declare(strict_types = 1);
namespace Inspire\Core\Logger;

/**
 * Description of Log
 *
 * @author aalves
 */
abstract class Log
{

    /**
     * Collection of Log objects
     *
     * @var array
     */
    public static array $logChannels = [];

    /**
     * Call private function statically.It will work only for default channel
     *
     * @param string $method
     * @param array $arguments
     */
    public static function __callstatic(string $method, array $arguments)
    {
        /**
         * If channel default isn't initialized yet
         */
        if (! isset(self::$logChannels['default'])) {
            self::$logChannels['default'] = new Logger('default');
        }
        call_user_func_array([
            self::$logChannels['default'],
            $method
        ], $arguments);
        /**
         * Return Log object
         */
        return self::$logChannels['default'];
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
        if (! isset(self::$logChannels[$channel])) {
            self::$logChannels[$channel] = new Logger($channel);
        }
        /**
         * Return Log object
         */
        return self::$logChannels[$channel];
    }
}

