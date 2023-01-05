<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

use \League\Flysystem\{
    Filesystem,
    Ftp\FtpAdapter,
    Ftp\FtpConnectionOptions,
    Ftp\FtpConnectionProvider,
    Ftp\NoopCommandConnectivityChecker
};

class Ftp extends BaseFs
{

    /**
     * @var FtpAdapter|null|null
     */
    private ?FtpAdapter $adapter = null;

    /**
     * Initialize filesystem adapter
     */
    public function init(array $settings): bool
    {
        /**
         * If there's no proxy settings, use common FTP Adapter of Flysystem
         */
        if (!isset($settings['proxy'])) {
            $this->adapter = new FtpAdapter(
                FtpConnectionOptions::fromArray($settings),
                new FtpConnectionProvider(),
                new NoopCommandConnectivityChecker(),
                new \League\Flysystem\UnixVisibility\PortableVisibilityConverter()
            );
            $this->filesystem = new Filesystem($this->adapter);
        } else {
            /**
             * Use custom CurlFtpAdapter
             */
            $this->filesystem = new Filesystem(
                new CurlFtpAdapter($settings)
            );
        }
        $this->rootPath = rtrim($settings['root'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return true;
    }
}
