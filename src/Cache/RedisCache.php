<?php
declare(strict_types = 1);
namespace Inspire\Core\Cache;

use Cache\Adapter\Redis\RedisCachePool;

/**
 * Description of RedisCache
 *
 * @author aalves
 */
final class RedisCache extends RedisCachePool
{

    /**
     * Get list size
     *
     * @param string $item
     * @return string|null
     */
    public function lLen(string $item)
    {
        return $this->cache->lLen($item);
    }

    /**
     * Call protected/ private methods
     *
     * @param string $item
     * @return string|null
     */
    public function __call(string $name, array $args)
    {
        return call_user_func_array([
            $this,
            $name
        ], $args);
    }

    /**
     *
     * {@inheritdoc}
     */
    protected function lRange(string $name, int $start, int $end)
    {
        return $this->cache->lRange($name, $start, $end);
    }

    /**
     *
     * {@inheritdoc}
     */
    protected function rPush(string $name, $value)
    {
        $this->cache->rPush($name, $value);
    }

    /**
     *
     * {@inheritdoc}
     */
    protected function lIndex(string $name, int $index)
    {
        return $this->cache->lIndex($name, $index);
    }

    /**
     *
     * {@inheritdoc}
     */
    protected function lPop(string $name, int $count = null)
    {
        if ($count === null) {
            return $this->cache->lPop($name);
        } else {
            // PHP Redis have not support for it, yet.
            // Available on Redis server since version 6.2
            return $this->cache->lPop($name, $count);
        }
    }

    /**
     *
     * {@inheritdoc}
     */
    protected function getConnection()
    {
        return $this->cache;
    }
}

