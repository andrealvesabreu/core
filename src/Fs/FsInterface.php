<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

interface FsInterface
{
    /**
     * Get a file contents
     */
    public function get(string $path): ?string;
    /**
     * Set contents to file, if it not exists
     */
    public function set(string $path, string $contents): ?bool;
    /**
     * Put contents to file, overwriting any data 
     */
    public function put(string $path, string $contents): ?bool;

    /**
     * Get a file contents
     */
    public function copy(string $source, string $destination);

    /**
     * Get a file contents
     */
    public function move(string $source, string $destination);

    /**
     * Delete file or directory
     */
    public function delete(string $path);
    /**
     * Delete file or directory
     */
    public function list(?string $path = null): ?array;
    /**
     * Create directory
     */
    public function mkdir(string $path): ?bool;
    /**
     * Create directory
     */
    public function chdir(string $path);
    /**
     * Check if file exists
     */
    public function fileExists(string $path): ?bool;
    /**
     * Check if directory exists
     */
    public function directoryExists(string $path): ?bool;
}
