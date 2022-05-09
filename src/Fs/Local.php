<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

use \League\Flysystem\{
    Filesystem,
    Local\LocalFilesystemAdapter
};

/**
 * @method static string get(string $path)
 */
class Local extends BaseFs
{

    /**
     * Initialize filesystem adapter
     */
    public function init(array $settings): bool
    {
        if (
            !is_readable($settings['root']) ||
            !is_dir($settings['root'])
        ) {
            throw new \Exception('Base dir does not exists or is not readable');
        }
        /**
         * If there's no permission settings
         */
        if (!\Inspire\Support\Arrays::has($settings, 'mod')) {
            $this->filesystem =
                new Filesystem(
                    new LocalFilesystemAdapter(
                        $settings['root'] // Determine root directory
                    )
                );
        } else {
            $this->filesystem = new Filesystem(
                new LocalFilesystemAdapter(
                    $settings['root'], // Determine root directory
                    $settings['permissions'] // Determine permissions
                )
            );
        }
        $this->rootPath = rtrim($settings['root'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return true;
    }
}
