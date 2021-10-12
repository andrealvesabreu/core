<?php
declare(strict_types = 1);
namespace Inspire\Core\Logger;

use Psr\Log\LogLevel;
use Monolog\Logger;
use Inspire\Core\Factories\FactoryLogger;

/**
 * Description of Arrays
 *
 * @author aalves
 */
class Log
{

    /**
     * Collection of Logger objects
     *
     * @var array
     */
    private static $logStream = [];

    /**
     * Get Logger by its name
     *
     * @param string $level
     * @return Logger|NULL
     */
    public static function getLogStream(string $level): ?Logger
    {
        return self::$logStream[$level] ?? null;
    }

    /**
     * Set log level INFO
     */
    public static function info()
    {
        self::setLog(LogLevel::INFO, func_get_args());
    }

    /**
     * Set log level DEBUG
     */
    public static function debug(): void
    {
        self::setLog(LogLevel::DEBUG, func_get_args());
    }

    /**
     * Set log level CRITICAL
     */
    public static function critical(): void
    {
        self::setLog(LogLevel::CRITICAL, func_get_args());
    }

    /**
     * Set log level ALERT
     */
    public static function alert(): void
    {
        self::setLog(LogLevel::ALERT, func_get_args());
    }

    /**
     * Set log level LOG
     */
    public static function log(): void
    {
        self::setLog(LogLevel::LOG, func_get_args());
    }

    /**
     * Set log level EMERGENCY
     */
    public static function emergency(): void
    {
        self::setLog(LogLevel::EMERGENCY, func_get_args());
    }

    /**
     * Set log level WARNING
     */
    public static function warning(): void
    {
        self::setLog(LogLevel::WARNING, func_get_args());
    }

    /**
     * Set log level ERROR
     */
    public static function error(): void
    {
        self::setLog(LogLevel::ERROR, func_get_args());
    }

    /**
     * Set log level NOTICE
     */
    public static function notice(): void
    {
        self::setLog(LogLevel::NOTICE, func_get_args());
    }

    /**
     * Set all logs
     */
    private static function setLog(string $level, array $messages)
    {
        if (! self::getLogStream($level)) {
            if (($logger = FactoryLogger::create($level)) !== null) {
                self::$logStream[$level] = $logger;
            } else {
                var_dump("ERROR");
                return;
            }
        }
        foreach ($messages as $message) {
            self::$logStream[$level]->$level($message);
        }
    }
}

