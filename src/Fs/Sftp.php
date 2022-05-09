<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

use League\Flysystem\{
    Filesystem,
    PhpseclibV3\SftpAdapter,
    PhpseclibV3\SftpConnectionProvider,
    UnixVisibility\PortableVisibilityConverter
};

class Sftp extends BaseFs
{
    /**
     * Initialize filesystem adapter
     */
    public function init(array $settings): bool
    {
        /**
         * If there's no permission settings
         */
        if (!isset($settings['mod'])) {
            $this->filesystem = new Filesystem(
                new SftpAdapter(
                    new SftpConnectionProvider(
                        $settings['host'], // host (required)
                        $settings['username'], // username (required)
                        $settings['password'], // password (optional, default: null) set to null if privateKey is used
                        $settings['pkey'] ?? null, // private key (optional, default: null) can be used instead of password, set to null if password is set
                        $settings['passkey'] ?? null, // passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
                        $settings['port'] ?? 22, // port (optional, default: 22)
                        $settings['use_agent'] ?? false, // use agent (optional, default: false)
                        $settings['timeout'] ??  30, // timeout (optional, default: 10)
                        $settings['max_tries'] ?? 10, // max tries (optional, default: 4)
                        $settings['fingerprint'] ?? null, // host fingerprint (optional, default: null),
                        null, // connectivity checker (must be an implementation of 'League\Flysystem\PhpseclibV2\ConnectivityChecker' to check if a connection can be established (optional, omit if you don't need some special handling for setting reliable connections)
                    ),
                    $settings['root'] // root path (required)
                )
            );
        } else {
            $this->filesystem = new Filesystem(
                new SftpAdapter(
                    new SftpConnectionProvider(
                        $settings['host'], // host (required)
                        $settings['username'], // username (required)
                        $settings['password'], // password (optional, default: null) set to null if privateKey is used
                        $settings['pkey'] ?? null, // private key (optional, default: null) can be used instead of password, set to null if password is set
                        $settings['passkey'] ?? null, // passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
                        $settings['port'] ?? 22, // port (optional, default: 22)
                        $settings['use_agent'] ?? false, // use agent (optional, default: false)
                        $settings['timeout'] ??  30, // timeout (optional, default: 10)
                        $settings['max_tries'] ?? 10, // max tries (optional, default: 4)
                        $settings['fingerprint'] ?? null, // host fingerprint (optional, default: null),
                        null, // connectivity checker (must be an implementation of 'League\Flysystem\PhpseclibV2\ConnectivityChecker' to check if a connection can be established (optional, omit if you don't need some special handling for setting reliable connections)
                    ),
                    $settings['root'], // root path (required)
                    PortableVisibilityConverter::fromArray([
                        'file' => [
                            'public' => 0640,
                            'private' => 0604,
                        ],
                        'dir' => [
                            'public' => 0740,
                            'private' => 7604,
                        ],
                    ])
                )
            );
        }
        $this->rootPath = rtrim($settings['root'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return true;
    }
}
