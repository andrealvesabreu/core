<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Scheduler;

class Scheduler
{

    /**
     * List all processes started on this instance
     *
     * @var array
     */
    protected static ?array $processes = [];

    /**
     * List all running process on this instances
     *
     * @var array
     */
    private static ?array $running = [];

    /**
     * List last time when each process ran
     *
     * @var array
     */
    private static ?array $last_run_at = [];

    /**
     * Last time when process list was updated
     *
     * @var string
     */
    private static ?string $last_list_updated = null;

    /**
     * Interval to execute a function to update process list
     *
     * @var string
     */
    private static ?string $time_update = null;

    /**
     * A function to update process list
     *
     * @var mixed
     */
    private static $update_callback = null;

    /**
     * Argument list to use in each process
     *
     * @var array
     */
    public static ?array $index = [];

    /**
     * Index in $index of last process started
     *
     * @var int
     */
    private static ?int $last_started = 0;

    /**
     * Max number of process running
     *
     * @var int
     */
    protected static ?int $max_processes = 10;

    /**
     * Min difference between last execution and next execution, in seconds
     *
     * @var int
     */
    protected static ?int $min_interval = 300;

    /**
     * Time between check routines to run next process (time to sleep till check again if already there are some process to start)
     *
     * @var int
     */
    protected static ?int $sleep = null;

    /**
     * Loop function to keep running as a process manager
     *
     * @return void
     */
    public static function run(): void
    {
        /**
         * Check if process list was updated.
         * It must run an update on process list if it was never ran and a update function was defined
         */
        if (self::$update_callback !== null && self::$time_update !== null) {
            /**
             * Run update function and register time when it was done
             */
            call_user_func(self::$update_callback);
            self::$last_list_updated = time();
        }
        /**
         * Walks through $index create each process with its arguments
         */
        foreach (self::$index as $args) {
            /**
             * If process ID not exists, start it
             */
            if (! \Inspire\Support\Arrays::exists(self::$processes, $args->getCmdId())) {
                self::$processes[$args->getCmdId()] = new Process($args);
                self::$last_run_at[$args->getCmdId()] = 0;
            }
        }
        /**
         * Start and endless loop to keep running
         */
        do {
            /**
             * Remove from running list all process that were already ends
             */
            if (! empty(self::$running)) {
                foreach (self::$running as $process) {
                    if (! $process->isRunning()) {
                        self::$last_run_at[$process->getCmdId()] = time();
                        unset(self::$running[$process->getCmdId()]);
                        // $cmdId = $process->getCmdId();
                        // self::$processes[$cmdId] = new Process($cmd, $self::$index[$cmdId], $cmdId);
                    }
                }
            }
            /**
             * Check how many process can start now, checking difference between
             * how many can run at same time and how many are running, yet
             */
            $total_process = self::$max_processes - count(self::$running);
            // If can start some process
            if ($total_process > 0) {
                $min_time = time() - self::$min_interval; // 5 minutos
                foreach (self::$index as $idx => $d_index) {
                    if (! isset(self::$running[$d_index->getCmdId()]) && // If it isn't running
                    self::$last_run_at[$d_index->getCmdId()] < $min_time) { // And if time between last execution to then was elapsed
                                                                            // echo "START {$d_index->getCmdId()}\n";
                        /**
                         * Start this process
                         */
                        self::$processes[$d_index->getCmdId()]->start();
                        self::$running[$d_index->getCmdId()] = &self::$processes[$d_index->getCmdId()];
                        self::$last_started = $idx;
                        $total_process --;
                        if ($total_process == 0) {
                            self::$index = array_merge(array_slice(self::$index, $idx + 1), array_slice(self::$index, 0, $idx + 1));
                            break;
                        }
                    }
                }
            }
            /**
             * Check a update function and time that its ran to update process list again
             */
            if (self::$update_callback !== null && self::$time_update !== null) {
                // If never ran, only set as updated now (Consider that on starts, it will be updated)
                if (self::$last_list_updated === null) {
                    self::$last_list_updated = time();
                } // If time between updates was elapsed
                else if (self::$last_list_updated + self::$time_update <= time()) {
                    call_user_func(self::$update_callback);
                    self::$last_list_updated = time();
                }
            }
            /**
             * Time to sleep till next loop
             */
            if (self::$sleep !== null) {
                usleep(self::$sleep);
            }
        } while (true);
    }

    /**
     * Set a function to update process list every time when self::$time_update was elapsed
     *
     * @param Callable $fn
     * @param int $time
     * @return void
     */
    public static function updateCallback(Callable $fn, int $time): void
    {
        self::$update_callback = $fn;
        self::$time_update = $time;
    }
}
