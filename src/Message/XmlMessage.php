<?php
declare(strict_types = 1);
namespace Inspire\Core\Message;

/**
 * Description of XmlMessage
 *
 * @author aalves
 */
class XmlMessage extends ArrayMessage implements MessageInterface
{

    /**
     * Store a SimpleXMLElement implementation data of XML loaded
     *
     * @var \SimpleXMLElement|null
     */
    protected ?\SimpleXMLElement $xml = null;

    /**
     * Store the XML representation string
     *
     * @var string|null
     */
    protected ?string $stringXml = null;

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
    public function unserialize($data)
    {
        if ($this->load($data)) {
            $this->data = $this->xml;
        } else {
            $this->data = null;
        }
    }

    /**
     * Get all data
     *
     * {@inheritdoc}
     * @see \Inspire\Core\Message\MessageInterface::getData()
     */
    public function getData()
    {
        return $this->stringXml;
    }

    /**
     * Return data from specified field
     *
     * {@inheritdoc}
     * @see \Inspire\Core\Message\MessageInterface::get()
     */
    public function get(string $field, ?string $default = null)
    {
        return $this->xpath(str_replace('.', '/', $field)) ?? $default;
    }

    /**
     * Load string XML
     *
     * @param string $xml
     * @param string $sxclass
     * @param bool $nsattr
     * @param mixed $flags
     * @throws \Exception
     * @return bool
     */
    public function load(string $xml, string $sxclass = '\SimpleXMLElement', bool $nsattr = false, $flags = null): bool
    {
        $xml = trim($xml);
        /**
         * Validating arguments
         */
        // Class to map
        if (empty($sxclass) || ! is_string($sxclass) || ! class_exists($sxclass)) {
            throw new \Exception("{$sxclass} must be a SimpleXMLElement or a derived class.");
        }
        // Input XML
        if (! is_string($xml) || empty($xml)) {
            throw new \Exception("{$xml} must be a non-empty string.");
        }
        // Load XML if URL is provided as XML
        if (preg_match('~^https?://[^\s]+$~i', $xml) || is_readable($xml)) {
            $xml = file_get_contents($xml);
        }
        // Let's drop namespace definitions
        if (stripos($xml, 'xmlns=') !== false) {
            $xml = preg_replace('~[\s]+xmlns=[\'"].+?[\'"]~i', '', $xml);
        }
        // Change namespaced attributes
        $matches = [];
        if (preg_match_all('~xmlns:([a-z0-9]+)=~i', $xml, $matches)) {
            $namespaces = array_unique($matches[1]);
            foreach ($namespaces as $namespace) {
                $escaped_namespace = preg_quote($namespace, '~');
                $xml = preg_replace('~[\s]xmlns:' . $escaped_namespace . '=[\'].+?[\']~i', null, $xml);
                $xml = preg_replace('~[\s]xmlns:' . $escaped_namespace . '=["].+?["]~i', null, $xml);
                $xml = preg_replace('~([\'"\s])' . $escaped_namespace . ':~i', '$1' . $namespace . '_', $xml);
            }
        }
        // Let's change <namespace:tag to <namespace_tag ns="namespace"
        $regexfrom = sprintf('~<([a-z0-9]+):%s~is', null);
        // $regexto = strlen($nsattr) ? '<$1_$2 ' . $nsattr . '="$1"' : '<$1_';
        $xml = preg_replace($regexfrom, '', $xml);
        // Let's change </namespace:tag> to </namespace_tag>
        $xml = preg_replace('~</([a-z0-9]+):~is', '</$1_', $xml);
        // Default flags to use
        if (empty($flags)) {
            $flags = LIBXML_COMPACT | LIBXML_NOBLANKS | LIBXML_NOCDATA;
        }
        libxml_use_internal_errors(true);
        // Now load and return (namespaceless)
        $output = simplexml_load_string($xml, $sxclass, $flags);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        if (! empty($errors)) {
            throw new \Exception("{$xml} is not a valid XML.");
        }
        $this->xml = $output;
        return true;
    }

    /**
     * Get values from XML elements matched by XPath
     *
     * @param string $xpath
     * @param int $index
     * @return NULL|array
     */
    public function xpath(string $xpath, ?int $index = null)
    {
        if ($this->xml === null) {
            throw new \Exception("You must load a XML before get its values.");
        }
        $out = [];
        if ($index !== null) {
            $out = null;
        }
        $result = $this->xml->xpath("/{$xpath}");
        if ($result && ! empty($result)) {
            if ($index !== null) {
                if (isset($result[$index])) {
                    $out = $result[$index]->count() > 1 || ($result[$index]->count() > 0 && ! isset($result[$index]->{0})) ? json_decode(json_encode($result[$index]), true) : (string) $result[$index];
                } else {
                    $out = null;
                }
            } else {
                foreach ($result as $res) {
                    $out[] = json_decode(json_encode($res), true);
                }
            }
        } else {
            $out = null;
        }
        return $out;
    }
}