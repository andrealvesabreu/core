<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Certificate;

/**
 * Description of VerificationInterface
 *
 * @author aalves
 */
interface VerificationInterface
{

    const SIGNATURE_CORRECT = 1;

    const SIGNATURE_INCORRECT = 0;

    const SIGNATURE_ERROR = - 1;

    /**
     * Verify signature
     *
     * @link http://php.net/manual/en/function.openssl-verify.php
     * @param string $data
     * @param string $signature
     * @param int $algorithm
     *            [optional] For more information see the list of Signature Algorithms.
     * @return bool Returns true if the signature is correct, false if it is incorrect
     * @throws \Inspire\Core\Security\Exceptions\CertificateException An error has occurred when verify signature
     */
    public function verify(string $data, string $signature, int $algorithm = OPENSSL_ALGO_SHA1);
}
