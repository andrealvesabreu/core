<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

use Inspire\Config\Config;

final class FileFactory
{

    /**
     * Create a queue instance with $config configuration
     *
     * @param array $config
     * @param string $handlerName
     * @return BaseQueue|NULL
     */
    public static function create(string $handlerName, ?array $config = null): ?BaseFs
    {
        if ($config === null) {
            $settings = Config::get("filesystem.{$handlerName}");
            if ($settings === null) {
                throw new \Exception("Filesystem configuration not found");
            }
        } else {
            $settings = $config;
        }
        /**
         * Specific configuration for each filesystem adapter
         */
        switch ($settings['adapter']) {
                /**
             * Using Local filesystem adapter
             */
            case 'local':
                $filesystem = new Local();
                if ($filesystem->init($settings)) {
                    return $filesystem;
                }
                break;
                /**
                 * Using Ftp filesystem adapter
                 */
            case 'ftp':
                $filesystem = new Ftp();
                if ($filesystem->init($settings)) {
                    return $filesystem;
                }
                break;
                /**
                 * Using Ftp filesystem adapter
                 */
            case 'sftp':
                $filesystem = new Sftp();
                if ($filesystem->init($settings)) {
                    return $filesystem;
                }
                break;
                /**
                 * Using Ftp filesystem adapter
                 */
            case 's3':
                $filesystem = new S3();
                if ($filesystem->init($settings)) {
                    return $filesystem;
                }
                break;
        }
        throw new \Exception("Could not initialize a filesystem handler.");
    }
}
