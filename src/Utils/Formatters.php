<?php
declare(strict_types = 1);
namespace Inspire\Core\Utils;

/**
 * Description of Formatter
 *
 * @author aalves
 */
class Formatters
{

    /**
     * Formatting CPF/CNPJ
     *
     * @param string $doc
     * @return string
     */
    public static function cpfCnpj(string $doc): string
    {
        $doc = Strings::integer($doc);
        return Strings::format($doc, Strings::length($doc) == 11 ? '999.999.999-99' : '99.999.999/9999-99');
    }

    /**
     * Formatting plates
     *
     * @param string $placa
     * @return string
     */
    public static function plate(string $placa): string
    {
        return Strings::format(preg_replace("/[^A-Z0-9]/", "", strtoupper($placa)), 'AAA-9999');
    }

    /**
     * Formatting CPF
     *
     * @param string $cep
     * @return string
     */
    public static function cep(string $cep): string
    {
        return Strings::format(Strings::integer($cep), '99.999-999');
    }

    /**
     * Formatting $str with $format mask
     *
     * @param string $str
     * @param string $format
     * @return string
     */
    public static function custom(string $str, string $format): string
    {
        return Strings::format($str, $format);
    }
}
