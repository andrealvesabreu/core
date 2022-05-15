<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;


class FtpCurl
{
    /**
     * Curl resource handler
     * @var resource
     */
    protected $curl;

    /**
     * Global options to use in any request
     * @var array
     */
    protected $options;

    /**
     * Last curl response HTTP code
     * @var null
     */
    protected $lastCode = null;

    /**
     * @param  array  $options  Array of the Curl options, where key is a CURLOPT_* constant
     */
    public function __construct(?array $options = null)
    {
        $this->curl = curl_init();
        $this->options = $options ?? [];
    }


    /**
     * Destroy object, closing curl
     */
    public function __destruct()
    {
        if (
            (is_resource($this->curl) && get_resource_type($this->curl) == 'curl') || //For PHP < 8.0
            $this->curl instanceof \CurlHandle // For PHP >= 8.0
        ) {
            curl_close($this->curl);
        }
    }

    /**
     * Set the Curl options.
     *
     * @param  array  $options  Array of the Curl options, where key is a CURLOPT_* constant
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    /**
     * Set the Curl option.
     *
     * @param  int  $key  One of the CURLOPT_* constant
     * @param  mixed  $value  The value of the CURL option
     */
    public function setOption($key, $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * Returns the value of the option.
     *
     * @param  int  $key  One of the CURLOPT_* constant
     * @return mixed|null The value of the option set, or NULL, if it does not exist
     */
    public function getOption($key)
    {
        if (!$this->hasOption($key)) {
            return null;
        }
        return $this->options[$key];
    }
    public function getLastCode()
    {
        return $this->lastCode;
    }

    /**
     * Checking if the option is set.
     *
     * @param  int  $key  One of the CURLOPT_* constant
     * @return bool
     */
    public function hasOption($key): bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * Remove the option.
     *
     * @param  int  $key  One of the CURLOPT_* constant
     */
    public function removeOption($key): void
    {
        if ($this->hasOption($key)) {
            unset($this->options[$key]);
        }
    }

    /**
     * Calls curl_exec and returns its result.
     *
     * @param  array  $options  Array where key is a CURLOPT_* constant
     * @return mixed Results of curl_exec
     */
    public function exec($options = [])
    {
        $options = array_replace($this->options, $options);
        if (
            (is_resource($this->curl) && get_resource_type($this->curl) == 'curl') || //For PHP < 8.0
            $this->curl instanceof \CurlHandle // For PHP >= 8.0
        ) {
            curl_setopt_array($this->curl, $options);
            $result = curl_exec($this->curl);
            $this->lastCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            curl_reset($this->curl);
            return $result;
        }
        return null;
    }
}
