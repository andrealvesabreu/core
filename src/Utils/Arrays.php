<?php
declare(strict_types = 1);
namespace Inspire\Core\Utils;

/**
 * Description of Arrays
 *
 * @author aalves
 */
class Arrays extends \Illuminate\Support\Arr
{

    /**
     * Merge arrays removing all duplicated values
     * Before remove duplicated values, it will merge all arrays into a single array
     *
     * @return array
     */
    public static function mergeUnique(): array
    {
        if (empty(func_get_args())) {
            return [];
        }
        return array_unique(call_user_func_array('array_merge', func_get_args()));
    }

    /**
     * Array filter recursive
     * Example of a minimum length filter function callback
     * function($a) {
     * return is_array($a) || strlen($a) > 2;
     * }
     *
     * @param array $input
     *            Array where apply filter
     * @param callable $callback
     *            Some filter function
     * @param boolean $removeEmpty
     *            Define if empty sub arrays must be removed
     * @return array
     */
    public static function rcFilter(array $input, ?callable $callback = null, bool $removeEmpty = true): array
    {
        foreach ($input as $k => &$value) {
            if (is_array($value)) {
                $value = self::rcFilter($value, $callback, $removeEmpty);
                if ($removeEmpty && empty($value)) {
                    unset($input[$k]);
                }
            }
        }
        if ($callback === null) {
            $callback = function ($a) {
                return strlen($a) > 0;
            };
        }
        return array_filter($input, function ($vl) use ($callback) {
            return is_array($vl) || $callback($vl);
        });
    }

    /**
     * Array push recursive (put multidimensional into a single dimensional array)
     *
     * @param array $input
     *            Array to change to a single dimension
     * @param string $prefix
     *            A prefix to set in each index
     * @return array
     */
    public static function rcPush(array $input, ?string $prefix = null): array
    {
        $ar = [];
        foreach ($input as $i => $value) {
            if (is_array($value)) {
                if ($prefix === null) {
                    $ar = array_merge($ar, self::rcPush($value, $i));
                } else {
                    $ar = array_merge($ar, self::rcPush($value, "{$prefix}_{$i}"));
                }
            } else {
                if ($prefix === null || empty($prefix)) {
                    $ar[$i] = $value;
                } else {
                    $ar["{$prefix}_{$i}"] = $value;
                }
            }
        }
        return $ar;
    }

    /**
     * Array implode recursive (implode all elements from array)
     *
     * @param string $separator
     *            Separator to join elements of array
     * @param array $input
     *            The array to join elements
     * @return string
     */
    public static function rcImplode(string $separator = ',', array $input): ?string
    {
        $arr = [];
        foreach ($input as $ar) {
            if (is_array($ar)) {
                $arr[] = self::rcImplode($separator, $ar);
            } else {
                $arr[] = $ar;
            }
        }
        return implode($separator, $arr);
    }

    /**
     * Expand single dimensional array to multi dimensional, using $separator to split key in sub indexes
     *
     * @param array $array
     * @param array $separator
     * @return array
     */
    public static function expand(array $array, ?string $separator = null): array
    {
        if ($separator !== null && $separator != '.') {
            $combined = array_combine(str_replace($separator, '.', array_keys($array)), $array);
        } else {
            $combined = array_combine(array_keys($array), $array);
        }
        $output = [];
        foreach ($combined as $index => $val) {
            Arrays::set($output, $index, $val);
        }
        return $output;
    }

    /**
     * Check if array has sub arrays
     *
     * @param array $arr
     * @return array
     */
    public static function hasNextLevel(array $arr): bool
    {
        return count($arr) != count($arr, COUNT_RECURSIVE);
    }

    /**
     * Remove null values from array
     *
     * @param array $arr
     * @return array
     */
    public static function filterNull(array $arr): array
    {
        if (self::hasNextLevel($arr)) {
            return self::rcFilter($arr, function ($el) {
                return ! is_null($el);
            }, false);
        }
        return array_filter($arr, function ($v) {
            return ! is_null($v);
        });
    }

    /**
     * Checagem de indice de array
     *
     * @param string $key
     * @param array $arr
     * @return mixed
     */
    public static function keyCheck(string $key, array $arr)
    {
        return Arrays::has($arr, $key);
    }

    /**
     * Sort an array using a model
     *
     * @param array $data
     * @param array $model
     * @return array
     */
    public static function sortByModel(array $data, array $model): array
    {
        $tmp = [];
        foreach ($data as $k => $v) {
            if (is_int($k) && is_array($v)) {
                $tmp[$k] = self::sortByModel($v, $model[0] ?? $model);
            } else {
                foreach ($model as $km => $vm) {
                    if (is_array($vm)) {
                        if (is_int($km)) {
                            $tmp[$km] = self::sortByModel($v, $vm);
                        } else {
                            if (self::keyCheck($km, $data)) {
                                $tmp[$km] = self::sortByModel($data[$km], $vm);
                            }
                        }
                    } else if (self::keyCheck($km, $data)) {
                        $tmp[$km] = $data[$km];
                    }
                }
            }
        }
        return $tmp;
    }
}

