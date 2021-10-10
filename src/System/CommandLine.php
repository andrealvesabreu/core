<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

use Inspire\Core\Utils\Arrays;

/**
 *
 * @author aalves
 *        
 */
class CommandLine extends \CommandLine
{

    /**
     * Check if a field exists in command line arguments
     *
     * @param string $field
     * @return bool
     */
    public static function hasArg(string $field = null): bool
    {
        if ($field === null) {
            return self::$args !== null && is_array(self::$args) && ! empty(self::$args);
        }

        return self::$args !== null && Arrays::exists(self::$args, $field);
    }

    /**
     * Get a specified index from command line arguments
     *
     * @param string $field
     * @return string|null
     */
    public static function getArg(string $field = null): ?string
    {
        if ($field === null) {
            return self::$args;
        } else {
            return self::$args[$field] ?? null;
        }
    }

    /**
     * Parse array as command line key=value pair string
     *
     * @param array $args
     * @return string|NULL
     */
    public static function array2cli(array $args): ?string
    {
        $output = [];
        foreach ($args as $k => $v) {
            if (is_array($v)) {
                $v = implode(',', $v);
            }
            $output[] = "{$k}={$v}";
        }
        return empty($output) ? '' : implode(' ', $output);
    }
}

