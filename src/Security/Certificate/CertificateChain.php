<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Certificate;

/**
 * Description of CertificateChain
 *
 * @author aalves
 */
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
