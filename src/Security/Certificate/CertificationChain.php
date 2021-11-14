<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Certificate;

/**
 * Class for management and inclusion of certification chains to the public keys
 * of digital certificates model A1 (PKCS # 12)
 *
 * @category NFePHP
 * @package NFePHP\Common\CertificationChain
 * @copyright Copyright (c) 2008-2016
 * @license http://www.gnu.org/licenses/lesser.html LGPL v3
 * @author Roberto L. Machado <linux.rlm at gmail dot com>
 * @link http://github.com/nfephp-org/sped-common for the canonical source repository
 */
class CertificationChain
{

    /**
     *
     * @var string
     */
    private ?string $rawKey = '';

    /**
     *
     * @var array of PublicKeys::class
     */
    private array $chainKeys = [];

    /**
     * Certification Chain Keys constructor
     *
     * @param string $chainkeysstring
     */
    public function __construct($chainkeysstring = null)
    {
        if (! empty($chainkeysstring)) {
            $this->rawKey = $chainkeysstring;
            $this->loadListChain();
        }
    }

    /**
     * Add new certificate to certification chain
     *
     * @param string $content
     *            Certificate in DER, CER or PEM format
     * @return array
     */
    public function add(string $content): array
    {
        // verify format of certificate content if binary convert to PEM
        if ($this->isBinary($content)) {
            $content = base64_encode($content);
            $content = rtrim(chunk_split(preg_replace('/[\r\n]/', '', $content), 64, PHP_EOL));
            $content = <<<CONTENT
            -----BEGIN CERTIFICATE-----
            {$content}
            -----END CERTIFICATE-----
            CONTENT;
        }
        return $this->loadList($content);
    }

    /**
     * Detects if string contains binary characters
     *
     * @param string $str
     * @return bool
     */
    private function isBinary(string $str): bool
    {
        return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
    }

    /**
     * Remove certificate from certification chain by there common name
     */
    public function removeExpiredCertificates()
    {
        foreach ($this->chainKeys as $key => $publickey) {
            if ($publickey->isExpired()) {
                unset($this->chainKeys[$key]);
            }
        }
    }

    /**
     * List certificates from actual certification chain
     *
     * @return array
     */
    public function listChain(): array
    {
        return $this->chainKeys;
    }

    /**
     * Retuns all certificates in chain as string
     *
     * @return string
     */
    public function __toString(): string
    {
        $this->rawString();
        return $this->rawKey;
    }

    /**
     * Returns a array for build extracerts in PFX files
     *
     * @return array
     */
    public function getExtraCertsForPFX(): array
    {
        $ec = [];
        $args = [];
        $list = $this->chainKeys;
        foreach ($list as $cert) {
            $ec[] = "{$cert}";
        }
        if (! empty($ec)) {
            $args = [
                'extracerts' => $ec
            ];
        }
        return $args;
    }

    /**
     * Load chain certificates from string to array of PublicKey::class
     */
    private function loadListChain()
    {
        $arr = explode("-----END CERTIFICATE-----", $this->rawKey);
        foreach ($arr as $a) {
            if (strlen($a) > 20) {
                $cert = "{$a}-----END CERTIFICATE-----\n";
                $this->loadList($cert);
            }
        }
    }

    /**
     * Load PublicKey::class with certificates from chain
     *
     * @param string $certificate
     * @return array
     */
    private function loadList(string $certificate): array
    {
        $publickey = new PublicKey($certificate);
        $this->chainKeys[$publickey->commonName] = $publickey;
        return $this->chainKeys;
    }

    /**
     * Generate chain certificates as raw string
     */
    private function rawString()
    {
        $this->rawKey = '';
        foreach ($this->chainKeys as $publickey) {
            $this->rawKey .= $publickey;
        }
    }
}
