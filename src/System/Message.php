<?php
declare(strict_types = 1);
namespace Inspire\Core\System;

/**
 * Description of Message
 *
 * @author aalves
 */
abstract class Message
{

    // System message type
    const TYPE_SYSTEM = 1;

    // HTTP message type
    const TYPE_HTTP = 2;

    // Exception message type
    const TYPE_EXCEPTION = 3;

    // Code for emergency
    const MSG_EMERGENCY = 0;

    // Code for alert
    const MSG_ALERT = 1;

    // Code for critical error
    const MSG_CRITICAL = 2;

    // Code for general error
    const MSG_ERROR = 3;

    // Code for warnings
    const MSG_WARNING = 4;

    // Code for notices
    const MSG_NOTICE = 5;

    // Code for infos
    const MSG_INFO = 6;

    // Code for debug
    const MSG_DEBUG = 7;

    // Code for ok message interchange (default)
    const MSG_OK = 10;

    /**
     * Message code
     *
     * @var int
     */
    protected int $code = Message::MSG_OK;

    /**
     * Message type
     *
     * @var int
     */
    protected ?int $type = null;

    /**
     * Message text
     *
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * Code tranliterable for system
     *
     * @var string|null
     */
    protected ?string $systemCode = null;

    /**
     * Code tranliterable for system
     *
     * @var bool|null
     */
    protected ?bool $status = null;

    /**
     * Extra data
     *
     * @var array|NULL
     */
    protected ?array $extra = null;

    /**
     *
     * @return Message|array|\RuntimeException
     */
    public function getMessage(bool $asArray = true)
    {
        switch ($this->type) {
            case Message::SYSTEM_MESSAGE:
                return SystemMessage::get($this, $asArray);
            case Message::HTTP_MESSAGE:
                return HttpMessage::get($this, $asArray);
            case Message::SYSTEM_MESSAGE:
                return ExceptionMessage::get($this, $asArray);
            default:
                return new \RuntimeException("Unespected message type: {$this->type}");
        }
    }

    /**
     * Return message as array
     *
     * @param Message $message
     * @return array
     */
    protected function get(Message $message): array
    {
        return [
            'code' => $message->code,
            'message' => $message->message,
            'sys_code' => $message->systemCode,
            'status' => $message->status,
            'extra' => $message->extra ?? null
        ];
    }

    /**
     * Get message type
     *
     * @return int|NULL
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * Get message code
     *
     * @return int|NULL
     */
    public function getCode(): ?int
    {
        return $this->code;
    }

    /**
     * Get message system code
     *
     * @return string|NULL
     */
    public function getSystemCode(): ?string
    {
        return $this->systemCode;
    }

    /**
     * Get message text
     *
     * @return string|NULL
     */
    public function __toString(): ?string
    {
        return $this->message;
    }

    /**
     * Get extra data
     *
     * @return array|NULL
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }

    /**
     * Set extra data
     *
     * @param array $extra
     */
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
    }

    /**
     * Get message status
     *
     * @return bool|null
     */
    public function isOk(): ?bool
    {
        return $this->status;
    }
}

