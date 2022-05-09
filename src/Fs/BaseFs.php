<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

use League\Flysystem\FilesystemException;
use Nette\Utils\FileSystem;

class BaseFs implements FsInterface
{

    /**
     * Current dir path
     */
    protected string $currentPath = '';

    /**
     * Root dir path
     */
    protected string $rootPath = '';

    /**
     * File system 
     */
    protected ?\League\Flysystem\Filesystem $filesystem = null;

    /**
     * Get a file contents
     */
    public function get(string $path): ?string
    {
        return $this->filesystem->read($this->relativeToRoot($path));
    }

    /**
     * Get a file contents
     */
    public function set(string $path, string $contents): ?bool
    {
        $path = $this->relativeToRoot($path);
        if (!$this->filesystem->fileExists($path)) {
            $this->filesystem->write($path, $contents);
            return true;
        }
        return false;
    }

    /**
     * Get a file contents
     */
    public function put(string $path, string $contents): ?bool
    {
        $this->filesystem->write($this->relativeToRoot($path), $contents);
        return true;
    }

    /**
     * Get a file contents
     */
    public function copy(string $source, string $destination)
    {
        $this->filesystem->copy(
            $this->relativeToRoot($source),
            $this->relativeToRoot($destination)
        );
    }

    /**
     * Get a file contents
     */
    public function move(string $source, string $destination)
    {
        $this->filesystem->move(
            $this->relativeToRoot($source),
            $this->relativeToRoot($destination)
        );
    }

    /**
     * Get a file contents
     */
    public function delete(string $path)
    {
        $this->filesystem->delete($this->relativeToRoot($path));
    }

    /**
     * Get a file contents
     */
    public function list(?string $path = null): ?array
    {
        $list = [];
        try {
            $path = $this->relativeToRoot($path ?? '');
            $listing = $this->filesystem->listContents($path, false);
            foreach ($listing as $item) {
                if ($item instanceof \League\Flysystem\FileAttributes) {
                    $list[] = [
                        'type' => $item->type(),
                        'name' => $item->path(),
                        'visibility' => $item->visibility(),
                        'lastModified' => $item->lastModified(),
                        'fileSize' => $item->fileSize()
                    ];
                } elseif ($item instanceof \League\Flysystem\DirectoryAttributes) {
                    $list[] = [
                        'type' => $item->type(),
                        'name' => $item->path(),
                        'visibility' => $item->visibility(),
                        'lastModified' => $item->lastModified()
                    ];
                }
            }
        } catch (FilesystemException $exception) {
            return [];
        }
        return $list;
    }

    /**
     * Change directory
     */
    public function chdir(string $path)
    {
        if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
            $this->currentPath = ltrim(FileSystem::normalizePath($path), DIRECTORY_SEPARATOR);
        } else {
            $this->currentPath = ltrim(FileSystem::normalizePath($this->currentPath . DIRECTORY_SEPARATOR . $path), DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Change directory
     * @param string $path
     * 
     * @return bool|null
     */
    public function mkdir(string $path): ?bool
    {
        $this->filesystem->createDirectory($this->relativeToRoot($path), [
            'visibility' =>    'public',
            'directory_visibility' => 'public'
        ]);
        return $this->filesystem->directoryExists($this->relativeToRoot($path));
    }

    /**
     * Translate an argument path to relative path from root
     */
    protected function relativeToRoot(string $path): string
    {
        return FileSystem::normalizePath($this->currentPath . DIRECTORY_SEPARATOR . $path);
    }
}
