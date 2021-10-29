<?php
declare(strict_types = 1);
namespace Inspire\Core\Message;

/**
 * Description of LoggerFactory
 *
 * @author aalves
 */
interface MessageInterface
{

    public function __construct($data);

    public function serialize(): ?string;

    public function unserialize(string $data);

    public function getData();

    public function get(string $field);

    public function set(string $field, string $value);

    public function add(string $field, string $value);
}

