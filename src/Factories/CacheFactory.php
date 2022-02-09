<?php
declare(strict_types = 1);
namespace Inspire\Core\Factories;

use Inspire\Core\Cache\CacheInterface;
use Inspire\Core\Cache\RedisCache;
use Inspire\Core\Cache\ArrayCache;
use Cache\Adapter\Common\AbstractCachePool;

/**
 * Description of CacheFactory
 *
 * @author aalves
 */
abstract class CacheFactory
{

    /**
     * Get logger instance
     *
     * @param string $level
     * @return CacheInterface|null
     */
    public static function create(?string $channel = null): ?AbstractCachePool
    {
        if (($cache = \Inspire\Support\Config::get("cache")) !== null) {
            $channel = $channel === 'default' ? null : $channel;
            if (isset($cache[$channel]) && isset($cache[$channel]['driver'])) {
                $config = $cache[$channel];
                switch ($cache[$channel]['driver']) {
                    case 'redis':
                        try {
                            $redis = new \Redis();
                            $redis->connect($config['host'], $config['port']);
                            if (isset($config['pass']) && ! empty($config['pass'])) {
                                $redis->auth($config['pass']);
                            }
                            if (! $redis->isConnected()) {
                                throw new \Exception("Conexão invalida com Redis");
                            }
                            if (isset($config['database']) && $config['database'] !== null && is_int($config['database'])) {
                                $redis->select($config['database']);
                            }
                            $pool = new RedisCache($redis);
                            return $pool;
                        } catch (\RedisException $e) {
                            echo $e->getTraceAsString();
                            return null;
                        }
                    case 'array':
                        return new ArrayCache();
                    case 'memcached':
                        $memcached = new \Memcached();
                        $memcached->addServer($config['host'], $config['port']);
                        $statuses = $memcached->getStats();
                        if (! isset($statuses["{$config['host']}:{$config['port']}"]) || intval($statuses["{$config['host']}:{$config['port']}"]['pid']) < 0) {
                            throw new \Exception("Conexão invalida com memcached");
                        }
                        return $memcached;
                    default:
                        return null;
                }
            }
        }
        return null;
    }
}

