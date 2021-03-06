<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Security\Certificate;

/**
 * Interface for signature using digital certificates A1 (PKCS#12)
 *
 * @category NFePHP
 * @package NFePHP\Common\SignatureInterface
 * @copyright Copyright (c) 2008-2016
 * @license http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author Antonio Spinelli <tonicospinelli85 at gmail dot com>
 * @link http://github.com/nfephp-org/sped-common for the canonical source repository
 */
interface SignatureInterface
{

    /**
     * Generate signature.
     *
     * @link http://php.net/manual/en/function.openssl-sign.php
     * @param string $content
     * @param int $algorithm
     * @return string Returns the signature data.
     * @throws \Inspire\Core\Security\Exceptions\CertificateException
     */
    public function sign($content, $algorithm = OPENSSL_ALGO_SHA1);
}
