<?php
declare(strict_types = 1);
namespace Inspire\Core\Security\Exceptions;

/**
 * Description of Soap
 *
 * @author aalves
 */
class CouldNotLoadCertificate extends \Exception implements ExceptionInterface
{

    /**
     *
     * @param string $inputFile
     * @return \Inspire\Core\Security\Exceptions\CouldNotLoadCertificate
     */
    public static function cannotGetContents(string $inputFile = '')
    {
        return new static("Could not load a certificate with this filename `{$inputFile}`.");
    }
}
