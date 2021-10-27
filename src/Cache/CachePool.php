<?php
declare(strict_types = 1);
namespace Inspire\Core\Cache;

use Inspire\Core\Factories\CacheFactory;

/**
 * Description of Cache
 *
 * @author aalves
 */
final class CachePool
{

    /**
     * Collection of Cache objects
     *
     * @var array
     */
    private ?array $cacheStreams = [];

    /**
     * Channel who this Logger belong to
     *
     * @var string
     */
    private string $channel = 'default';

    public function __construct(?string $channel = null)
    {
        $channel = $channel ?? 'default';
        if (! isset($this->cacheStreams[$channel])) {
            if (($cache = CacheFactory::create($channel)) !== null) {
                $this->cacheStreams[$channel] = $cache;
            } else {
                return;
            }
        }
        $this->channel = $channel;
    }
}

