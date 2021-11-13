<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Exceptions;

/**
 * Description of Soap
 *
 * @author aalves
 */
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
