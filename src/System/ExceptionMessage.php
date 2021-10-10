<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

/**
 *
 * @author aalves
 *        
 */
class ExceptionMessage extends Message
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
        $this->type = Message::TYPE_EXCEPTION;
    }
}

