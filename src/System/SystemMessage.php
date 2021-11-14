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
     * @param bool $status
     */
    public function __construct(string $message, string $systemCode, int $code = Message::MSG_OK, ?bool $status = null)
    {
        $this->message = $message;
        $this->systemCode = $systemCode;
        $this->code = $code;
        $this->type = Message::TYPE_SYSTEM;
        if ($status !== null) {
            $this->status = $status;
        } else {
            $this->status = $code == Message::MSG_OK;
        }
    }
}

