<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

/**
 *
 * @author aalves
 *        
 */
class Environment
{

    /**
     * Define an environment
     *
     * @var string
     */
    private static string $env = 'api';

    /**
     * Set language
     *
     * @var string
     */
    private static string $language = Language::EN_US;

    /**
     * Debug mode
     *
     * @var boolean
     */
    private static bool $debug = true;

    /**
     * Set environment
     *
     * @param string $env
     */
    public static function setEnv(string $env): void
    {
        self::$env = $env;
    }

    /**
     * Return environment
     *
     * @return string
     */
    public static function getEnv(): string
    {
        return self::$env;
    }

    /**
     * Change language
     *
     * @param string $language
     */
    public static function setLanguage(string $language): void
    {
        self::$language = $language;
    }

    /**
     * Return current language
     *
     * @return string
     */
    public static function getLanguage(): string
    {
        return self::$language;
    }

    /**
     * Check if environment is in debug mode
     *
     * @return boolean
     */
    public static function isDebug(): bool
    {
        return self::$debug;
    }
}