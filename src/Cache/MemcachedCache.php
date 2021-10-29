<?php
declare(strict_types = 1);
namespace Inspire\Core\Cache;

use Cache\Adapter\Memcached\MemcachedCachePool;

/**
 * Description of MemcachedCache
 *
 * @author aalves
 */
final class MemcachedCache extends MemcachedCachePool
{

    public function lLen(string $item)
    {
        return 0;
    }
}

