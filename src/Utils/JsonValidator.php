<?php
declare(strict_types = 1);
namespace Inspire\Core\Utils;

use Exception;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\JsonPointer;

/**
 * Description of JsonValidator
 *
 * @author aalves
 */
class JsonValidator
{

    private static string $lang = \Inspire\Core\System\Language::EN_US;

    /**
     *
     * @var array
     */
    private static array $readable_messages = [
        \Inspire\Core\System\Language::PT_BR => [
            'minItems' => "O grupo '?' deve ter no mínimo ? elementos, mas apenas ? foram informados.",
            'maxItems' => "O grupo '?' deve ter no máximo ? elementos, mas foram informados ?.",
            'minLength' => "O campo '?' deve ter no mínimo ? caracteres, mas apenas ? foram informados.",
            'maxLength' => "O campo '?' deve ter no máximo ? caracteres, mas foram informados ?.",
            'format' => "O campo '?' deve ser preenchido no formato '?'.",
            'minimum' => "O valor do campo '?' deve ser no mínimo ?. Você informou ?.",
            'maximum' => "O valor do campo '?' deve ser no máximo ?. Você informou ?.",
            'pattern' => "O valor do campo '?' deve obedecer a E.R. ?.",
            'required' => "O campo '?' é obrigatório.",
            'type' => "O campo '?' deve ser um de [?], mas foi informado '?'.",
            'enum' => "O campo '?' deve ser preenchido com um dos seguintes valores: ?.",
            'oneOf' => "O campo '?' não corresponde a nenhum dos esquemas disponíveis."
        ],
        \Inspire\Core\System\Language::EN_US => [
            'minItems' => "The group '?' must have at least ? elements, but only ? was informed.",
            'maxItems' => "The group '?' must have at most ? elements, but ? was informed.",
            'minLength' => "The field '?' must have at least ? characters, but only ? was informed.",
            'maxLength' => "The field '?' must have at most ? characters, but ? was informed.",
            'format' => "The field '?' must be filled in '?' format.",
            'minimum' => "The value of the field '?' must be at least ?. ? was informed.",
            'maximum' => "The value of the field '?' must be at most ?. ? was informed.",
            'pattern' => "The value of the field '?' must match E.R. ?.",
            'required' => "The field '?' is required.",
            'type' => "The field '?' must be one of [?], but it was informed '?'.",
            'enum' => "The field '?' must be filled with one of the following values: ?.",
            'oneOf' => "The field '?' does not match any of the available schemes."
        ]
    ];

    /**
     * Registered errors
     *
     * @var array
     */
    private static ?ValidationError $errors = null;

    /**
     * Validate JSON string using a JSON schema
     *
     * @param string $data
     * @param string $schema
     * @param int $max_errors
     * @return boolean
     * @throws \Exception
     */
    public static function validateJson(string $data, string $schema, int $max_errors = 1): bool
    {
        try {
            self::$lang = \Inspire\Core\System\Environment::getLanguage();
            $vdata = json_decode($data);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \Exception('Input data is not a JSON string');
            }
            /**
             * Schema can be a file
             */
            if (preg_match('/^[\/\w\-. ]+$/', $schema)) {
                if (! file_exists($schema)) {
                    throw new Exception('Schema file not found');
                }
                $vschema = json_decode(file_get_contents($schema));
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new Exception('Invalid schema file');
                }
            } else {
                $vschema = json_decode($schema);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new Exception('Invalid schema string');
                }
            }
            /**
             * Apply JSON schema
             */
            $validator = new \Opis\JsonSchema\Validator();
            $validator->setMaxErrors(5);
            self::$errors = $validator->dataValidation($vdata, $vschema);
            return ! self::hasErrors();
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Verify if errors data are filled
     *
     * @return bool
     */
    public static function hasErrors(): bool
    {
        return self::$errors !== null;
    }

    /**
     * Return parsed error list
     *
     * @param array $errorList
     * @return array
     */
    public static function getReadableErrors(?ValidationError $errorList = null): ?array
    {
        $error = self::$errors;
        $formatter = new ErrorFormatter();

        $custom = function (ValidationError $error) use ($formatter) {
            switch ($error->keyword()) {
                /**
                 * Array limitations
                 */
                case 'minItems':
                case 'maxItems':
                    $values = array_values($error->args());
                    array_unshift($values, implode('->', $error->data()->fullPath()));
                    return preg_replace([
                        "/\?/",
                        "/\?/",
                        "/\?/"
                    ], //
                    $values, //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * String size limitations
                 */
                case 'minLength':
                case 'maxLength':
                    $values = array_values($error->args());
                    array_unshift($values, implode('->', $error->data()->fullPath()));
                    return preg_replace([
                        "/\?/",
                        "/\?/",
                        "/\?/"
                    ], //
                    $values, //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * String format error
                 */
                case 'format':
                    return preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        implode('->', $error->data()->fullPath()),
                        $error->args()[$error->keyword()]
                    ], //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * Number limitations
                 */
                case 'minimum':
                case 'maximum':
                    return preg_replace([
                        "/\?/",
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        implode('->', $error->data()->fullPath()),
                        $error->args()[substr($error->keyword(), 0, 3)],
                        $error->data()->value()
                    ], //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * Missing required fields
                 */
                case 'required':
                    return str_replace('?', //
                    ltrim(implode('->', $error->data()->fullPath()) . '->' . implode('->', $error->args()['missing']), '->'), //
                    self::$readable_messages[self::$lang]['required']);
                /**
                 * Type errors
                 */
                case 'type':
                    $info = $error->args();
                    if (is_array($info['expected'])) {
                        $info['expected'] = implode(', ', $info['expected']);
                    }
                    return preg_replace([
                        "/\?/",
                        "/\?/",
                        "/\?/"
                    ], //
                    array_merge([
                        implode('->', $error->data()->fullPath())
                    ], $info), //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * Error pattern matches
                 */
                case 'pattern':
                    return preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        implode('->', $error->data()->fullPath()),
                        $error->args()['pattern']
                    ], //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * Enumeration errors
                 */
                case 'enum':
                    $schema = $error->schema()->info();
                    return preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        implode('->', $error->data()->fullPath()),
                        '[' . implode(',', $schema->data()->enum) . ']'
                    ], //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
                /**
                 * Enumeration errors
                 */
                case 'oneOf':
                    $schema = $error->schema()->info();
                    return preg_replace([
                        "/\?/"
                    ], //
                    [
                        implode('->', $error->data()->fullPath())
                    ], //
                    self::$readable_messages[self::$lang][$error->keyword()], //
                    1);
            }
            return $error->message();
        };
        // $print($formatter->format($error, true, $custom, $custom_key));
        // print_r(array_values($formatter->format($error, false, $custom)));
        return array_values($formatter->format($error, false, $custom));
    }

    /**
     * Returns errors as validator fills
     *
     * @return array|null
     */
    public static function getErrors(): ?array
    {
        return is_array(self::$errors) && ! empty(self::$errors) ? self::$errors : null;
    }
}


