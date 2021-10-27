<?php
declare(strict_types = 1);
namespace Inspire\Core\Message;

/**
 * Description of LoggerFactory
 *
 * @author aalves
 */
class JsonMessage extends ArrayMessage implements MessageInterface
{

    /**
     * Return constents serialized as JSON
     *
     * {@inheritdoc}
     * @see \Inspire\Core\Message\MessageInterface::serialize()
     */
    public function serialize(): ?string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desserialize JSON data
     *
     * {@inheritdoc}
     * @see \Inspire\Core\Message\MessageInterface::unserialize()
     */
    public function unserialize(): ?string
    {
        $this->data = json_decode($this->data, true);
    }
}

