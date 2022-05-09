<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Security\Exceptions;

class CouldNotCreateCertificate extends \Exception implements ExceptionInterface
{

    /**
     *
     * @param string $content
     * @return \Inspire\Core\Security\Exceptions\CouldNotCreateCertificate
     */
    public static function invalidContent(string $content = '')
    {
        return new static("Could not create a certificate with content `{$content}`.");
    }
}
