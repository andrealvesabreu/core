<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

/**
 *
 * @author aalves
 *        
 */
class SystemMessage extends Message
{

    /**
     * Constructor
     *
     * @param string $message
     * @param string $systemCode
     * @param int $code
     */
    public function __construct(string $message, string $systemCode, int $code = Message::MSG_OK)
    {
        $this->message = $message;
        $this->systemCode = $systemCode;
        $this->code = $code;
        $this->type = Message::TYPE_SYSTEM;
    }

    // public function __construct(string $message, string $systemCode, int $code = Message::OK)
    // {
    // $this->message = $message;
    // $this->systemCode = $systemCode;
    // $this->code = $code;
    // }

    // /**
    // * Return core message like a SystemMessage
    // *
    // * @param Message $message
    // * @param bool $asArray
    // */
    // protected static function get(Message $message, bool $asArray)
    // {
    // if ($asArray) {
    // return parent::get();
    // }
    // return new static($message);
    // }
}

