<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Scheduler;

class Process
{

    /**
     * Command process PID
     *
     * @var int
     */
    private ?int $pid = null;

    /**
     * Last timestamp when process started
     *
     * @var int
     */
    private ?int $last_start_at = null;

    /**
     * Command line to execute
     *
     * @var Arguments
     */
    private ?Arguments $command = null;

    /**
     * Constructor Receive a instance of Arguments
     *
     * @param \Inspire\Core\Scheduler\Arguments $command
     */
    public function __construct(Arguments $command)
    {
        $this->command = $command;
    }

    /**
     * Get system PID to this process
     *
     * @return int
     */
    public function getPid(): int
    {
        return intval($this->pid);
    }

    /**
     * Get application identification to this command
     *
     * @return string
     */
    public function getCmdId(): string
    {
        return $this->command->getCmdId();
    }

    /**
     * Get command line of current process
     *
     * @return string
     */
    public function getCommand(): ?Arguments
    {
        return $this->command;
    }

    /**
     * Get last time when this command stated to run
     *
     * @return int
     */
    public function getLastStartAt(): int
    {
        return intval($this->last_start_at);
    }

    /**
     * Start process execution without process lock, getting its PID to process manager
     *
     * @return void
     */
    public function start(): void
    {
        $op = [];
        exec("nohup {$this->command->getFullCommand()} > /dev/null 2>&1 & echo $!", $op);
        $this->pid = (int) $op[0];
        $this->last_start_at = time();
    }

    /**
     * Check if a process is running yet, using its PID
     *
     * @return boolean
     */
    public function isRunning(): bool
    {
        $op = [];
        exec("ps -p {$this->pid}", $op);
        if (!isset($op[1])) {
            return false;
        }
        return true;
    }
}
