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

    public function lLen(string $item)
    {
        return $this->cache->lLen($item);
    }
}

