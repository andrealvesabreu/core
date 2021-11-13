<?php
declare(strict_types = 1);
namespace Inspire\Core\Message;

/**
 * Description of MessageInterface
 *
 * @author aalves
 */
interface MessageInterface
{

    public function __construct($data);

    public function serialize(): ?string;

    public function unserialize($data);

    public function getData();

    public function get(string $field);

    public function set(string $field, string $value);

    public function add(string $field, string $value);
}

