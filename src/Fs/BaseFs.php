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
     * 
     * @param string $path
     * 
     * @return string|null
     */
    public function get(string $path): ?string
    {
        return $this->filesystem->read($this->relativeToRoot($path));
    }

    /**
     * Set contents to file
     * 
     * @param string $path
     * @param string $contents
     * 
     * @return bool|null
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
     * Put contents to file
     * 
     * @param string $path
     * @param string $contents
     * 
     * @return bool|null
     */
    public function put(string $path, string $contents): ?bool
    {
        $this->filesystem->write($this->relativeToRoot($path), $contents);
        return true;
    }

    /**
     * Create a copy of one file
     * 
     * @param string $source
     * @param string $destination
     * 
     * @return [type]
     */
    public function copy(string $source, string $destination)
    {
        $this->filesystem->copy(
            $this->relativeToRoot($source),
            $this->relativeToRoot($destination)
        );
    }

    /**
     * Move a file to another name/address
     *
     * @param string $source
     * @param string $destination
     * 
     * @return [type]
     */
    public function move(string $source, string $destination)
    {
        $this->filesystem->move(
            $this->relativeToRoot($source),
            $this->relativeToRoot($destination)
        );
    }

    /**
     * Delete one file
     * 
     * @param string $path
     * 
     * @return [type]
     */
    public function delete(string $path)
    {
        $this->filesystem->delete($this->relativeToRoot($path));
    }

    /**
     * Delete one directory
     * 
     * @param string $path
     * 
     * @return [type]
     */
    public function deleteDirectory(string $path)
    {
        $this->filesystem->deleteDirectory($this->relativeToRoot($path));
    }

    // /**
    //  * Get mime type of one file. Not implemented in CurlFtpAdapter
    //  */
    // public function mimeType(string $path)
    // {
    //     $this->filesystem->mimeType($this->relativeToRoot($path));
    // }

    /**
     * List files and folders
     * 
     * @param string|null $path
     * 
     * @return array|null
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
     * Change current directory
     * 
     * @param string $path
     * 
     * @return [type]
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
     * Create directory
     * 
     * @param string $path
     * 
     * @return bool|null
     */
    public function mkdir(string $path): ?bool
    {
        if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
            $previouDir = $this->currentPath;
            $this->chdir('/');
            $this->filesystem->createDirectory($this->relativeToRoot($path), [
                'visibility' =>    'public',
                'directory_visibility' => 'public'
            ]);
            $exists = $this->directoryExists($path);
            $this->chdir($previouDir);
        } else {
            $this->filesystem->createDirectory($this->relativeToRoot($path), [
                'visibility' =>    'public',
                'directory_visibility' => 'public'
            ]);
            $exists = $this->directoryExists($path);
        }
        return $exists;
    }

    /**
     * Check if direcoty exists
     * 
     * @param string $path
     * 
     * @return bool|null
     */
    public function directoryExists(string $path): ?bool
    {
        return $this->filesystem->directoryExists($this->relativeToRoot($path));
    }

    /**
     * Check if file exists
     * 
     * @param string $path
     * 
     * @return bool|null
     */
    public function fileExists(string $path): ?bool
    {
        return $this->filesystem->fileExists($this->relativeToRoot($path));
    }

    /**
     * Translate an argument path to relative path from root
     * 
     * @param string $path
     * 
     * @return string
     */
    protected function relativeToRoot(string $path): string
    {
        return FileSystem::normalizePath($this->currentPath . DIRECTORY_SEPARATOR . $path);
    }
}