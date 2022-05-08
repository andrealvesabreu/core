<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Security\Exceptions;

class CouldNotLoadCertificate extends \Exception implements ExceptionInterface
{

    /**
     *
     * @param string $inputFile
     * @return \Inspire\Core\Security\Exceptions\CouldNotLoadCertificate
     */
    public static function cannotGetContents(string $inputFile = '')
    {
        return new static("Could not load a certificate with this filename `{$inputFile}`.");
    }
}
