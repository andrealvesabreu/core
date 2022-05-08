<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Security\Certificate;

class CertificateChain
{

    /**
     * Certificate chains
     *
     * @var array
     */
    protected array $certificates = [];

    /**
     *
     * @param Certificate $certificate
     * @return string
     */
    public static function fetchForCertificate(Certificate $certificate): CertificateChain
    {
        return (new static($certificate))->getContentOfCompleteChain();
    }

    /**
     *
     * @param Certificate $certificate
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificates[] = $certificate;
    }

    /**
     *
     * @return string
     */
    public function getContentOfCompleteChain(): string
    {
        while ($this->lastCertificate()->hasParentInTrustChain()) {
            $this->certificates[] = $this->lastCertificate()->fetchParentCertificate();
        }
        $certs = "";
        foreach ($this->certificates as $certificate) {
            $certs .= $certificate->getContents();
        }
        return $certs;
    }

    /**
     */
    protected function lastCertificate()
    {
        return $this->certificates[count($this->certificates) - 1];
    }
}
