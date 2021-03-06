<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Security\Certificate;

use Inspire\Core\Security\Exceptions\CertificateException;

/**
 * Class for management and use of digital certificates A1 (PKCS # 12)
 *
 * @category NFePHP
 * @package NFePHP\Common\ProvateKey
 * @copyright Copyright (c) 2008-2016
 * @license http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author Antonio Spinelli <tonicospinelli85 at gmail dot com>
 * @link http://github.com/nfephp-org/sped-common for the canonical source repository
 */
class PrivateKey implements SignatureInterface
{

    /**
     *
     * @var string
     */
    private string $rawKey;

    /**
     *
     * @var resource
     */
    private $resource;

    /**
     *
     * @var array
     */
    private array $details = [];

    /**
     * PublicKey constructor.
     *
     * @param string $privateKey
     *            Content of private key file
     */
    public function __construct($privateKey)
    {
        $this->rawKey = $privateKey;
        $this->read();
    }

    /**
     * Get a private key
     *
     * @link http://php.net/manual/en/function.openssl-pkey-get-private.php
     * @return void
     * @throws CertificateException An error has occurred when get private key
     */
    protected function read()
    {
        if (!$resource = openssl_pkey_get_private($this->rawKey)) {
            throw CertificateException::getPrivateKey();
        }
        $this->details = openssl_pkey_get_details($resource);
        $this->resource = $resource;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function sign($content, $algorithm = OPENSSL_ALGO_SHA1)
    {
        $encryptedData = '';
        if (!openssl_sign($content, $encryptedData, $this->resource, $algorithm)) {
            throw CertificateException::signContent();
        }
        return $encryptedData;
    }

    /**
     * Return the modulus of private key
     *
     * @return string
     */
    public function modulus(): ?string
    {
        if (empty($this->details['rsa']['n'])) {
            return '';
        }
        return base64_encode($this->details['rsa']['n']);
    }

    /**
     * Return the expoent of private key
     *
     * @return string
     */
    public function expoent(): string
    {
        if (empty($this->details['rsa']['e'])) {
            return '';
        }
        return base64_encode($this->details['rsa']['e']);
    }

    /**
     * Return raw private key
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->rawKey;
    }
}
