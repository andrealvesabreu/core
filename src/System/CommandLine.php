<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\System;

use Inspire\Support\Arrays;

/**
 * CommandLine class
 * Changed by aalves
 *
 * Command Line Interface (CLI) utility class.
 *
 * @author Patrick Fisher <patrick@pwfisher.com>
 * @since August 21, 2009
 * @see https://github.com/pwfisher/CommandLine.php
 */
class CommandLine
{

    private static $args;

    /**
     * PARSE ARGUMENTS
     *
     * This command line option parser supports any combination of three types of options
     * [single character options (`-a -b` or `-ab` or `-c -d=dog` or `-cd dog`),
     * long options (`--foo` or `--bar=baz` or `--bar baz`)
     * and arguments (`arg1 arg2`)] and returns a simple array.
     *
     * [pfisher ~]$ php test.php --foo --bar=baz --spam eggs
     * ["foo"] => true
     * ["bar"] => "baz"
     * ["spam"] => "eggs"
     *
     * [pfisher ~]$ php test.php -abc foo
     * ["a"] => true
     * ["b"] => true
     * ["c"] => "foo"
     *
     * [pfisher ~]$ php test.php arg1 arg2 arg3
     * [0] => "arg1"
     * [1] => "arg2"
     * [2] => "arg3"
     *
     * [pfisher ~]$ php test.php plain-arg --foo --bar=baz --funny="spam=eggs" --also-funny=spam=eggs \
     * > 'plain arg 2' -abc -k=value "plain arg 3" --s="original" --s='overwrite' --s
     * [0] => "plain-arg"
     * ["foo"] => true
     * ["bar"] => "baz"
     * ["funny"] => "spam=eggs"
     * ["also-funny"]=> "spam=eggs"
     * [1] => "plain arg 2"
     * ["a"] => true
     * ["b"] => true
     * ["c"] => true
     * ["k"] => "value"
     * [2] => "plain arg 3"
     * ["s"] => "overwrite"
     *
     * Not supported: `-cd=dog`.
     *
     * @author Patrick Fisher <patrick@pwfisher.com>
     * @since August 21, 2009
     * @see https://github.com/pwfisher/CommandLine.php
     * @see http://www.php.net/manual/en/features.commandline.php #81042 function arguments($argv) by technorati at gmail dot com, 12-Feb-2008
     *      #78651 function getArgs($args) by B Crawford, 22-Oct-2007
     * @usage               $args = CommandLine::parseArgs($_SERVER['argv']);
     */
    public static function parseArgs($argv = null)
    {
        $argv = $argv ? $argv : $_SERVER['argv'];

        array_shift($argv);
        $out = array();
        foreach ($argv as $arg) {
            // -k=value -abc || --foo --bar=baz
            if (substr($arg, 0, 1) == '-') {
                do {
                    $arg = substr($arg, 1);
                } while (substr($arg, 0, 1) == '-');
            }
            $eqPos = strpos($arg, '=');
            // foo
            if ($eqPos === false) {
                $value = isset($out[$arg]) ? $out[$arg] : true;
                $out[$arg] = $value;
            } // bar=baz
            else {
                $key = substr($arg, 0, $eqPos);
                $value = substr($arg, $eqPos + 1);
                $out[$key] = $value;
            }
        }
        self::$args = $out;
        return $out;
    }

    /**
     * GET BOOLEAN
     */
    private static function getBoolean($key, $default = false)
    {
        if (! isset(self::$args[$key])) {
            return $default;
        }
        $value = self::$args[$key];

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            $map = array(
                'y' => true,
                'n' => false,
                'yes' => true,
                'no' => false,
                'true' => true,
                'false' => false,
                '1' => true,
                '0' => false,
                'on' => true,
                'off' => false
            );
            if (isset($map[$value])) {
                return $map[$value];
            }
        }

        return $default;
    }

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
            if (strpos($v, ' ') !== false) {                
                $output[] = "{$k}='" . trim(str_replace('\'', '\\\'', $v), '\'').'\'';
            } else {
                $output[] = "{$k}=" . str_replace('\'', '\\\'', $v);
            }
        }
        return empty($output) ? '' : implode(' ', $output);
    }
}

