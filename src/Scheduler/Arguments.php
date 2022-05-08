<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Scheduler;

class Arguments
{

    /**
     * List process arguments
     *
     * @var array
     */
    private ?array $arguments = null;

    /**
     * Base command
     *
     * @var string
     */
    private ?string $command = null;

    /**
     * Command ID to process manager
     *
     * @var string
     */
    private ?string $cmd_id = null;

    /**
     * Constructor
     *
     * @param string $command
     * @param array $arguments
     * @param string $cmd_id
     * @return void
     */
    public function __construct(string $command, ?array $arguments, string $cmd_id)
    {
        $this->command = $command;
        $this->arguments = $arguments;
        $this->cmd_id = $cmd_id;
    }

    /**
     * Get process arguments to command line interface
     *
     * @return string|null
     */
    public function getArgs(): ?string
    {
        return http_build_query($this->arguments, false, ' ');
    }

    /**
     * Set process arguments to command line interface
     *
     * @param array $args
     * @return void
     */
    public function setArgs(array $args): void
    {
        $this->arguments = $args;
    }

    /**
     * Get base command without arguments
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Get command with its arguments
     *
     * @return string
     */
    public function getFullCommand(): string
    {
        return $this->command . ' ' . \Inspire\Core\System\CommandLine::array2cli($this->arguments);
    }

    /**
     * Get command ID
     *
     * @return string
     */
    public function getCmdId(): string
    {
        return $this->cmd_id;
    }
}
