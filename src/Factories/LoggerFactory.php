<?php
declare(strict_types = 1);
namespace Inspire\Core\Factories;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

/**
 * Description of LoggerFactory
 *
 * @author aalves
 */
final class LoggerFactory
{

    /**
     * Get logger instance
     *
     * @param string $level
     * @return Logger|null
     */
    public static function create(string $level, ?string $channel = null): ?Logger
    {
        // if (($logger = Log::getLogStream($level, $channel)) !== null) {
        // return $logger;
        // }
        if (($logs = \Inspire\Core\System\Config::get("log")) !== null) {
            array_shift($logs);
            /**
             * Filter only configuratons applyable to this channel
             */
            $channel = $channel === 'default' ? null : $channel;
            if ($channel === null) {
                $logs = array_filter($logs, function ($item) {
                    return ! isset($item['channel']) || $item['channel'] === null || $item['channel'] === 'default';
                });
            } else {
                $logs = array_filter($logs, function ($item) use ($channel) {
                    return isset($item['channel']) && $item['channel'] == $channel;
                });
            }
            $index = array_search($level, array_column($logs, 'level'));
            if ($index !== false) {
                $settings = array_values($logs);
                $settings[$index]['name'] = array_keys($logs)[$index];
                $settings = $settings[$index];
                $formatter = new LineFormatter($settings['format'] ?? null, $settings['date_format'] ?? "Y-m-d H:i:s", true, true);
                $formatter->setDateFormat($settings['date_format'] ?? "Y-m-d H:i:s");

                $streamHandler = new RotatingFileHandler($settings['filename'], $settings['max_files'], $level, true, $settings['file_perms'] ?? 1363);
                $streamHandler->setFormatter($formatter);
                $logger = new Logger($settings['name']);
                $logger->pushHandler($streamHandler);
                return $logger;
            }
        }
        return null;
    }
}
