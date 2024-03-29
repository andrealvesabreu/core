<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

// use Aws\S3\S3Client;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\{
    AwsS3V3Adapter,
    PortableVisibilityConverter
};
use League\Flysystem\Filesystem;

class S3 extends BaseFs
{
    /**
     * Initialize filesystem adapter
     */
    public function init(array $settings): bool
    {
        $client = new S3Client([
            'version' => $settings['version'] ?? 'latest',
            'region' => $settings['region'],
            'credentials' => $settings['credentials']
        ]);
        if (!isset($settings['bucket'])) {
            $adapter = new AwsS3V3Adapter(
                $client,
                $settings['bucket']
            );
        } else {
            $adapter = new AwsS3V3Adapter(
                $client,
                $settings['bucket'],
                $settings['prefix'],
                new PortableVisibilityConverter(
                    $settings['visibility']
                )
            );
        }
        $this->filesystem = new Filesystem($adapter);
        $this->rootPath = '';
        $this->currentPath = $settings['prefix'];
        return true;
    }
}
