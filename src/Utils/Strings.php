<?php
declare(strict_types = 1);
namespace Inspire\Core\Utils;

/**
 * Description of Arrays
 *
 * @author aalves
 */
class Strings extends \Illuminate\Support\Str
{

    const STR_HEX = '0123456789ABCDEF';

    const STR_ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const STR_NUM = '1234567890';

    const STR_ALPHANUM = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    const STR_ALPHA_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const STR_ALPHA_LOWER = 'abcdefghijklmnopqrstuvwxyz';

    const STR_SYMBOLS = '{}[]()/\'"`~,;:.<>!@#$%&*-_=+?\\';

    /**
     * Format $str using $mask
     *
     * @param string $str
     * @param string $mask
     * @return string|null
     */
    public static function format(string $str, string $mask): ?string
    {
        $pMask = strlen($mask) - 1;
        $output = '';
        for ($a = strlen($str) - 1; $a >= 0; $a --, $pMask --) {
            if (! ctype_alnum(substr($mask, $pMask, 1))) {
                $output .= substr($mask, $pMask, 1);
                $pMask --;
            }
            if ($pMask > 0) {
                $output .= substr($str, $a, 1);
            } else {
                $output .= strrev(substr($str, 0, $a + 1));
                break;
            }
        }
        return strrev($output);
    }

    /**
     * Trim $char of sub string from index 0 till $length.
     * If $cut is true, then input $string will be cut from $length to end
     *
     * @param string $string
     * @param int $length
     * @param string $char
     * @return string|null
     */
    public static function substrTrim(string &$string, int $length, string $char = ' ', bool $cut = false): ?string
    {
        $str = substr($string, 0, $length);
        if ($cut) {
            $string = substr($string, $length);
        }
        return trim($str, $char);
    }

    /**
     * Left trim $char of sub string from index 0 till $length.
     * If $cut is true, then input $string will be cut from $length to end
     *
     * @param string $string
     * @param int $length
     * @param string $char
     * @param bool $cut
     * @return string|null
     */
    public static function substrLtrim(string &$string, int $length, string $char = ' ', bool $cut = false): ?string
    {
        $str = substr($string, 0, $length);
        if ($cut) {
            $string = substr($string, $length);
        }
        return ltrim($str, $char);
    }

    /**
     * Right trim $char of sub string from index 0 till $length.
     * If $cut is true, then input $string will be cut from $length to end
     *
     * @param string $string
     * @param int $length
     * @param string $char
     * @param bool $cut
     * @return string|null
     */
    public static function substrRtrim(string &$string, int $length, string $char = ' ', bool $cut = false): ?string
    {
        $str = substr($string, 0, $length);
        if ($cut) {
            $string = substr($string, $length);
        }
        return rtrim($str, $char);
    }

    /**
     * Get int value from string, removing removing its non numeric chars before
     *
     * @param string $string
     * @return int
     */
    public static function toInt(string $string): int
    {
        return intval(self::integer($string));
    }

    /**
     * Converte string para float
     *
     * @param string $string
     * @param int $d
     * @return float
     */
    public static function toFloat(string $string, int $d = 2): float
    {
        return floatval(self::decimal($string, $d));
    }

    /**
     * Remove non numeric chars from string
     * if $empty is false and $string is empty, then return 0.
     *
     * @param string $string
     * @param bool $empty
     * @return string
     */
    public static function integer(string $string, bool $empty = false): string
    {
        $val = preg_replace('/[^0-9]/', '', $string);
        if (! $empty) {
            $val = empty($val) ? '0' : $val;
        }
        return $val;
    }

    /**
     * Remove non decimal chars from string
     * String formatter in decimal machine format, with "dot" thousand separator
     *
     * @param string $string
     * @param int $decimals
     * @return string
     */
    public static function decimal(string $string, int $decimals = 2): string
    {
        $string = preg_replace('/[^0-9.,]/', '', $string);
        $val = empty($string) ? 0 : $string;
        if (strpos($string, ",") > 0) {
            $val = str_replace(",", ".", str_replace(".", "", $val));
        }
        return number_format($val, $decimals, ".", "");
    }

    /**
     * Generate random string using only $keyspace chars
     *
     * @param int $length
     * @param string $keyspace
     * @return string
     */
    public static function randomString(int $length = 10, string $keyspace = Strings::STR_NUM . Strings::STR_ALPHA . Strings::STR_SYMBOLS): string
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++ $i) {
            $str .= $keyspace[rand(0, $max)];
        }
        return $str;
    }

    /**
     * Remove all accents from $value
     *
     * @param mixed $value
     * @param bool $upper
     * @return mixed
     */
    public static function removeAccents($value, bool $upper = false)
    {
        if (is_string($value)) {
            if ($upper) {
                return mb_strtoupper(self::ascii(\ForceUTF8\Encoding::toUTF8($value)));
            }
            return self::ascii(\ForceUTF8\Encoding::toUTF8($value));
        }
        $string = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($m) {
            $len = strlen($m[2]);
            $result = "s:{$len}:\"{$m[2]}\";";
            return $result;
        }, self::ascii(\ForceUTF8\Encoding::toUTF8(serialize($value))));
        if ($upper) {
            return array_map('self::nestedUppercase', unserialize($string));
        }
        return unserialize($string);
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param string $value
     * @param string $language
     * @return string
     */
    public static function ascii($value, $language = 'en')
    {
        return \voku\helper\ASCII::to_ascii((string) $value, $language);
    }
}

