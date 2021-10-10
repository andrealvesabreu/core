<?php
declare(strict_types = 1);
namespace Inspire\Core\Utils;

/**
 * Description of Date
 *
 * @author aalves
 */
class Date extends \Nette\Utils\DateTime
{

    /**
     * Date constants
     */
    const DATE_ER_EN = '((([0-9][0-9](([02468][048])|([13579][26]))-02-29))|([0-9][0-9][0-9][0-9])-((((0[1-9])|(1[0-2]))-((0[1-9])|(1\d)|(2[0-8])))|((((0[13578])|(1[02]))-31)|(((0[1,3-9])|(1[0-2]))-(29|30)))))';

    const DATE_ER_PT = '((29[\/\-]02[\/\-]([0-9][0-9](([02468][048])|([13579][26]))))|((((0[1-9])|(1\d)|(2[0-8]))[\/\-]((0[1-9])|(1[0-2])))|((31[\/\-]((0[13578])|(1[02])))|((29|30)[\/\-]((0[1,3-9])|(1[0-2])))))[\/\-]([0-9][0-9][0-9][0-9]))';

    const DATE_STR_EN = 'Y-m-d';

    const DATETIME_STR_EN = 'Y-m-d H:i:s';

    const DATE_STR_PT = 'd/m/Y';

    const HOUR_STR = 'H:i:s';

    const DATE_DEFAULT = null;

    /**
     * Timezone list to Brazil
     *
     * @var array
     */
    private static $tzUFlist = [
        'AC' => 'America/Rio_Branco',
        'AL' => 'America/Maceio',
        'AM' => 'America/Manaus',
        'AP' => 'America/Belem',
        'BA' => 'America/Bahia',
        'CE' => 'America/Fortaleza',
        'DF' => 'America/Sao_Paulo',
        'ES' => 'America/Sao_Paulo',
        'GO' => 'America/Sao_Paulo',
        'MA' => 'America/Fortaleza',
        'MG' => 'America/Sao_Paulo',
        'MS' => 'America/Campo_Grande',
        'MT' => 'America/Cuiaba',
        'PA' => 'America/Belem',
        'PB' => 'America/Fortaleza',
        'PE' => 'America/Recife',
        'PI' => 'America/Fortaleza',
        'PR' => 'America/Sao_Paulo',
        'RJ' => 'America/Sao_Paulo',
        'RN' => 'America/Fortaleza',
        'RO' => 'America/Porto_Velho',
        'RR' => 'America/Boa_Vista',
        'RS' => 'America/Sao_Paulo',
        'SC' => 'America/Sao_Paulo',
        'SE' => 'America/Maceio',
        'SP' => 'America/Sao_Paulo',
        'TO' => 'America/Araguaina'
    ];

    /**
     * Use static date
     *
     * @var bool
     */
    public static $STATIC_DATE = true;

    /**
     * Date today
     *
     * @var string
     */
    private static $today = null;

    /**
     * Time of first call
     *
     * @var string
     */
    private static $now = null;

    /**
     * Get weekday name to specified date
     *
     * @param string $date
     * @return string
     */
    public static function weekday(string $date): string
    {
        if ($date === null) {
            return null;
        }
        return strtolower(strftime('%a', strtotime($date)));
    }

    /**
     * Get month name to specified date
     *
     * @param string $date
     * @return string
     */
    public static function month(string $date): string
    {
        if ($date === null) {
            return null;
        }
        return strtolower(strftime('%b', strtotime($date)));
    }

    /**
     * Set timezone based on $UF
     *
     * @param string $UF
     * @return string|null
     */
    public static function tzdBR(string $UF = "DF"): ?string
    {
        if ($UF === null || empty($UF) || ! self::keyCheck($UF, self::$tzUFlist)) {
            return null;
        }
        date_default_timezone_set(self::$tzUFlist[$UF]);
        return (string) date('P');
    }

    /**
     * Subtract days from a date
     *
     * @param int $days
     * @param string $date
     * @param string $format
     * @return string|null
     */
    public static function subDays(int $days, string $date = null, string $format = Date::DATE_STR_EN): ?string
    {
        $date = new \DateTime($date);
        $date->sub(new \DateInterval("P{$days}D"));
        return $date->format($format);
    }

    /**
     * Subtract months from a date
     *
     * @param int $months
     * @param string $date
     * @param string $format
     * @return string|null
     */
    public static function subMonths(int $months, string $date = null, string $format = Date::DATE_STR_EN): ?string
    {
        $date = new \DateTime($date);
        $date->sub(new \DateInterval("P{$months}M"));
        return $date->format($format);
    }

    /**
     * Subtract years from a date
     *
     * @param int $years
     * @param string $date
     * @param string $format
     * @return string|null
     */
    public static function subYears(int $years, string $date = null, string $format = Date::DATE_STR_EN): ?string
    {
        $date = new \DateTime($date);
        $date->sub(new \DateInterval("P{$years}Y"));
        return $date->format($format);
    }

    /**
     * Subtract minutes from a date
     *
     * @param int $minutes
     * @param string $date
     * @param string $format
     * @return string|null
     */
    public static function subMinutes(int $minutes, string $date = null, string $format = Date::DATETIME_STR_EN): ?string
    {
        $date = new \DateTime($date);
        $date->sub(new \DateInterval("PT{$minutes}M"));
        return $date->format($format);
    }

    /**
     * Subtract hours from a date
     *
     * @param int $hours
     * @param string $date
     * @param string $format
     * @return string|null
     */
    public static function subHours(int $hours, string $date = null, string $format = Date::DATETIME_STR_EN): ?string
    {
        $date = new \DateTime($date);
        $date->sub(new \DateInterval("PT{$hours}H"));
        return $date->format($format);
    }

    /**
     * Get only a section from data: d -> date, h -> hour, tz -> timezone
     *
     * @param string $date
     * @param string $field
     * @return string|NULL
     */
    public static function parseISO(string $date, string $field = null): ?string
    {
        $str = trim($date);
        if ($field === null) {
            return [
                'd' => strlen($str) >= 10 ? substr($str, 0, 10) : null,
                'h' => strlen($str) > 18 ? substr($str, 11, 8) : null,
                'tz' => strlen($str) > 19 ? substr($str, 19) : null
            ];
        }
        switch ($field) {
            case 'd':
                return strlen($str) >= 10 ? substr($str, 0, 10) : null;
            case 'h':
                return strlen($str) > 18 ? substr($str, 11, 8) : null;
            case 'tz':
                return strlen($str) > 19 ? substr($str, 19) : null;
            default:
                return null;
        }
    }
}
