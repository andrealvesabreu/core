<?php
declare(strict_types = 1);
namespace Inspire\Core\Logger;

use Psr\Log\LogLevel;
use Inspire\Core\Factories\LoggerFactory;

/**
 *
 * @author aalves
 *        
 */
final class Logger
{

    /**
     * Collection of Logger objects
     *
     * @var array
     */
    private ?array $logStreams = [];

    /**
     * Channel who this Logger belong to
     *
     * @var string
     */
    private string $channel = 'default';

    public function __construct(?string $channel = null)
    {
        $this->channel = $channel ?? 'default';
    }

    /**
     * Set log level INFO
     */
    public function info()
    {
        $this->setLog(LogLevel::INFO, func_get_args());
        return $this;
    }

    /**
     * Set log level DEBUG
     */
    public function debug()
    {
        $this->setLog(LogLevel::DEBUG, func_get_args());
        return $this;
    }

    /**
     * Set log level CRITICAL
     */
    public function critical()
    {
        $this->setLog(LogLevel::CRITICAL, func_get_args());
        return $this;
    }

    /**
     * Set log level ALERT
     */
    public function alert()
    {
        $this->setLog(LogLevel::ALERT, func_get_args());
        return $this;
    }

    /**
     * Set log level LOG
     */
    public function log()
    {
        $this->setLog(LogLevel::LOG, func_get_args());
        return $this;
    }

    /**
     * Set log level EMERGENCY
     */
    public function emergency()
    {
        $this->setLog(LogLevel::EMERGENCY, func_get_args());
        return $this;
    }

    /**
     * Set log level WARNING
     */
    public function warning()
    {
        $this->setLog(LogLevel::WARNING, func_get_args());
        return $this;
    }

    /**
     * Set log level ERROR
     */
    public function error()
    {
        $this->setLog(LogLevel::ERROR, func_get_args());
        return $this;
    }

    /**
     * Set log level NOTICE
     */
    public function notice()
    {
        $this->setLog(LogLevel::NOTICE, func_get_args());
        return $this;
    }

    /**
     * Set all logs
     */
    private function setLog(string $level, array $messages)
    {
        if (! isset($this->logStreams[$level])) {
            if (($logger = LoggerFactory::create($level, $this->channel)) !== null) {
                $this->logStreams[$level] = $logger;
            } else {
                return;
            }
        }
        foreach ($messages as $message) {
            $this->logStreams[$level]->$level($message);
        }
    }
}

