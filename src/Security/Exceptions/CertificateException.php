<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Exceptions;

/**
 * Description of Soap
 *
 * @author aalves
 */
class CertificateException extends \RuntimeException implements ExceptionInterface
{

    /**
     *
     * @return \Inspire\Core\Security\Exceptions\CertificateException
     */
    public static function unableToRead()
    {
        return new static('Unable to read certificate, ' . static::getOpenSSLError());
    }

    /**
     *
     * @return \Inspire\Core\Security\Exceptions\CertificateException
     */
    public static function unableToOpen()
    {
        return new static('Unable to open certificate, ' . static::getOpenSSLError());
    }

    /**
     *
     * @return \Inspire\Core\Security\Exceptions\CertificateException
     */
    public static function signContent()
    {
        return new static('An unexpected error has occurred when sign a content, ' . static::getOpenSSLError());
    }

    /**
     *
     * @return \Inspire\Core\Security\Exceptions\CertificateException
     */
    public static function getPrivateKey()
    {
        return new static('An error has occurred when get private key, ' . static::getOpenSSLError());
    }

    /**
     *
     * @return \Inspire\Core\Security\Exceptions\CertificateException
     */
    public static function signatureFailed()
    {
        return new static('An error has occurred when verify signature, ' . static::getOpenSSLError());
    }

    /**
     *
     * @return string
     */
    protected static function getOpenSSLError()
    {
        $error = 'get follow error: ';
        while ($msg = openssl_error_string()) {
            $error .= "($msg)";
        }
        return $error;
    }
}
