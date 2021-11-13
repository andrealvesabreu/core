<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Certificate;

use phpseclib3\File\X509;
use Inspire\Core\Security\Exceptions\ {
    CouldNotLoadCertificate,
    CouldNotCreateCertificate
};

class Certificate
{

    /**
     *
     * @var string|null
     */
    protected ?string $contents = null;

    /**
     * Load certificate from file
     *
     * @param string $inputFile
     * @return Certificate
     */
    public static function loadFromFile(string $inputFile): Certificate
    {
        $contents = null;
        if (file_exists($inputFile) && is_file($inputFile) && is_readable($inputFile)) {
            $contents = file_get_contents($inputFile);
        }
        if ($contents === null) {
            throw CouldNotLoadCertificate::cannotGetContents($inputFile);
        }
        return new static($contents);
    }

    /**
     * Load certificate from URL
     *
     * @param string $url
     * @return Certificate
     */
    public static function loadFromUrl(string $url): Certificate
    {
        return static::loadFromFile($url);
    }

    public function __construct(string $contents)
    {
        $certificate = $contents;
        // If we are missing the pem certificate header, try to convert it to a pem format first
        if (! empty($contents) && strpos($contents, '-----BEGIN CERTIFICATE-----') === false) {
            // Extract from either a PKCS#7 format or DER formatted contents
            $certs = self::convertPkcs72Pem($contents);
            $certificate = (isset($certs) && ! empty($certs)) ? $certs : $contents;
        }
        if (is_array($certificate)) {
            foreach ($certificate as $cert) {
                if (strpos($cert, "-----BEGIN ") !== false) {
                    $this->guardAgainstInvalidContents($cert, $contents);
                }
            }
        } else {
            if (strpos($certificate, "-----BEGIN ") !== false) {
                $this->guardAgainstInvalidContents($certificate, $contents);
            }
        }
        $this->contents = $certificate;
    }

    /**
     * Get the URL of the parent certificate.
     *
     * @return NULL|mixed
     */
    public function getParentCertificateUrl()
    {
        $certProperties = (new X509())->loadX509($this->contents);
        if (empty($certProperties['tbsCertificate']['extensions'])) {
            return null;
        }
        foreach ($certProperties['tbsCertificate']['extensions'] as $extension) {
            if ($extension['extnId'] == 'id-pe-authorityInfoAccess') {
                foreach ($extension['extnValue'] as $extnValue) {
                    if ($extnValue['accessMethod'] == 'id-ad-caIssuers') {
                        return $extnValue['accessLocation']['uniformResourceIdentifier'];
                    }
                }
            }
        }
        return null;
    }

    /**
     *
     * @return \Inspire\Core\Security\Certificate\Certificate
     */
    public function fetchParentCertificate()
    {
        return static::loadFromUrl($this->getParentCertificateUrl());
    }

    /**
     *
     * @return boolean|NULL|mixed
     */
    public function hasParentInTrustChain()
    {
        return $this->getParentCertificateUrl() ?? false;
    }

    /**
     *
     * @return string
     */
    public function getContents()
    {
        $output = "";
        if (is_array($this->contents)) {
            foreach ($this->contents as $cert) {
                // $x509 = new X509();
                // $output .= $x509->saveX509($x509->loadX509($cert)).PHP_EOL;
                $output .= $cert . PHP_EOL;
            }
        }
        return $output;
    }

    /**
     *
     * @param string $content
     * @param string $original
     */
    protected function guardAgainstInvalidContents(string $content, string $original)
    {
        if (! (new X509())->loadX509($content)) {
            throw CouldNotCreateCertificate::invalidContent($original);
        }
    }

    /**
     *
     * @param string $pkcs7
     * @return array|null
     */
    protected function convertPkcs72Pem(string $pkcs7): ?array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pkcs7_') . ".pb7";
        @file_put_contents($tempFile, $pkcs7);
        $result = @shell_exec("openssl pkcs7 -inform DER -outform PEM -in {$tempFile} -print_certs");
        @unlink($tempFile);
        $result = @explode("\n\n", $result);
        if (! empty($result) && is_array($result)) {
            foreach ($result as $k => $cert) {
                if (strlen($cert) < 100) {
                    unset($result[$k]);
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param string $der_data
     * @param string $type
     * @return string
     */
    protected function convertDer2Pem(string $der_data, string $type = 'CERTIFICATE'): string
    {
        $pem = chunk_split(base64_encode($der_data), 64, "\n");
        return "-----BEGIN {$type}-----\n{$pem}-----END {$type}-----\n";
    }
}
