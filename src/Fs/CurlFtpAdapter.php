<?php

declare(strict_types=1);

// Copyright (c) 2022 André Alves
// 
// This software is released under the MIT License.
// https://opensource.org/licenses/MIT

namespace Inspire\Core\Fs;

use DateTime;
use Generator;
use Inspire\Support\Arrays;
use League\Flysystem\{
    Config,
    FileAttributes,
    FilesystemAdapter,
    UnableToRetrieveMetadata,
};
use RuntimeException;

class CurlFtpAdapter  implements FilesystemAdapter
{

    /**
     * Operational system MS Windows constant
     */
    private const SYSTEM_TYPE_WINDOWS = 'windows';

    /**
     * Operational system UNIX family constant
     */
    private const SYSTEM_TYPE_UNIX = 'unix';

    /**
     * Connection configuration
     * @var array
     */
    private array $config;

    /**
     * Directory separaor
     * @var string
     */
    protected string $separator = '/';

    /**
     * Timestamp when it opened server connection
     * @var int|null|null
     */
    protected ?int $connectionTimestamp = null;

    /**
     * Construct receiving connection configuration
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Open a connection.
     * 
     * @return void
     */
    private function connect(): void
    {
        $this->connection = new FtpCurl([
            CURLOPT_URL => $this->getBaseUri(),
            CURLOPT_USERPWD => "{$this->getConfig('username')}:{$this->getConfig('password')}",
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT,
            CURLOPT_CONNECTTIMEOUT => $this->getConfig('timeout') ?? 60,
        ]);
        if (boolval($this->getConfig('ssl'))) {
            $this->connection->setOption(CURLOPT_USE_SSL, CURLFTPSSL_ALL);
        }

        if (!$this->getConfig('passive')) {
            $this->connection->setOption(CURLOPT_FTPPORT, '-');
        }

        if ($this->getConfig('ignorePassiveAddress')) {
            $this->connection->setOption(CURLOPT_FTP_SKIP_PASV_IP, $this->skipPasvIp);
        }

        $this->connection->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->connection->setOption(CURLOPT_SSL_VERIFYPEER, false);

        if ($proxyUrl = $this->getConfig('proxy.host', false)) {
            $proxyPort = $this->getConfig('proxy.port', false);
            $this->connection->setOption(CURLOPT_PROXY, $proxyPort ? "{$proxyUrl}:{$proxyPort}" : $proxyUrl);
            if ($proxyPort) {
                $this->connection->setOption(CURLOPT_PROXYPORT, $proxyPort);
            }
            $this->connection->setOption(CURLOPT_PROXYTYPE, 'HTTP');
            $this->connection->setOption(CURLOPT_HTTPPROXYTUNNEL, true);
            // $this->connection->setOption(CURLOPT_FTPSSLAUTH, CURLFTPAUTH_DEFAULT);
        }

        if ($username = $this->getConfig('proxy.username', false)) {
            $this->connection->setOption(CURLOPT_PROXYUSERPWD, "{$username}:{$this->getConfig('proxy.password', '')}");
        }

        $this->pingConnection();
        $this->connectionTimestamp = time();
        if ($this->getConfig('utf8', false)) {
            $this->setUtf8Mode();
        }
        $this->setConnectionRoot();
    }

    /**
     * Check the connection is established.
     * 
     * @return void
     */
    protected function pingConnection(): void
    {
        if ($this->connection->exec([
            CURLOPT_NOBODY => true
        ]) === false) {
            throw new RuntimeException("Could not connect to host: {$this->getConfig('host')}, port: {$this->getConfig('port')}");
        }
    }

    /**
     * Set the connection to UTF-8 mode.
     * 
     * @return void
     */
    protected function setUtf8Mode(): void
    {
        if (!boolval($this->getConfig('utf8'))) {
            return;
        }
        $response = $this->rawCommand('OPTS UTF8 ON');
        [$code, $message] = explode(' ', end($response), 2);
        if (!in_array($code, ['200', '202'])) {
            throw new RuntimeException(
                "Could not set UTF-8 mode for connection: {$this->getConfig('host')}::{$this->getConfig('port')}"
            );
        }
    }

    /**
     * Set the connection root.
     * 
     * @return void
     */
    protected function setConnectionRoot(): void
    {
        $root = $this->getConfig('root');
        if (empty($root)) {
            return;
        }
        $response = $this->rawCommand("CWD {$root}");
        [$code] = explode(' ', end($response), 2);
        if ((int) $code !== 250) {
            throw new RuntimeException("Root is invalid or does not exist: {$root}");
        }
    }

    /**
     * Create directory
     * 
     * @param string $path
     * @param Config $config
     * 
     * @return void
     */
    public function createDirectory(string $path, Config $config): void
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        // $this->getConnection()
        //     ->exec([
        //         CURLOPT_URL => "{$this->getBaseUri()}{$this->separator}" .  rawurlencode($requestPath) . $this->separator . '.',
        //         CURLOPT_FTP_CREATE_MISSING_DIRS => true,
        //     ]);
        $response = $this->rawCommand("MKD {$requestPath}");
        [$code] = explode(' ', end($response), 2);
    }

    /**
     * Check if directory exists
     * 
     * @param string $path
     * 
     * @return bool
     */
    public function directoryExists(string $path): bool
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        return false !== $this->getConnection()
            ->exec([
                CURLOPT_URL => "{$this->getBaseUri()}{$this->separator}" .  rawurlencode($requestPath) . $this->separator,
                CURLOPT_DIRLISTONLY => true
            ]);
    }

    /**
     * List files and folders in path
     * 
     * @param string $path
     * @param bool $deep
     * 
     * @return iterable
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        $result = $this->getConnection()
            ->exec([
                CURLOPT_URL => "{$this->getBaseUri()}{$requestPath}",
                CURLOPT_CUSTOMREQUEST => 'LIST -aln'
            ], true);
        if ($result) {
            yield from  $this->normalizeListing(explode(PHP_EOL, $result), '');
        }
        yield from [];
    }

    /**
     * Get the path prefix.
     *
     * @return string|null path prefix or null if pathPrefix is empty
     */
    public function getPathPrefix()
    {
        return $this->getConfig('root');
    }

    /**
     * Prefix a path.
     *
     * @param string $path
     *
     * @return string prefixed path
     */
    public function applyPathPrefix($path)
    {
        return $this->getPathPrefix() . ltrim($path, '\\/');
    }

    /**
     * Remove a path prefix.
     *
     * @param string $path
     *
     * @return string path without the prefix
     */
    public function removePathPrefix($path)
    {
        return substr($path, strlen($this->getPathPrefix()));
    }

    /**
     * Normalize listing data
     * 
     * @param array $listing
     * @param string $prefix
     * 
     * @return Generator
     */
    private function normalizeListing(array $listing, string $prefix = ''): Generator
    {
        $base = $prefix;
        foreach ($listing as $item) {
            if ($item === '' || preg_match('#.* \.(\.)?$|^total#', $item)) {
                continue;
            }

            if (preg_match('#^.*:$#', $item)) {
                $base = preg_replace('~^\./*|:$~', '', $item);
                continue;
            }
            yield $this->normalizeObject($item, $base);
        }
    }

    /**
     * Normalize list as objects
     * 
     * @param string $item
     * @param string $base
     * 
     * @return [type]
     */
    private function normalizeObject(string $item, string $base)
    {
        $systemType = $this->getConfig('systemType') ? $this->getConfig('systemType') : $this->detectSystemType($item);
        if ($systemType === self::SYSTEM_TYPE_UNIX) {
            return $this->normalizeUnixObject($item, $base);
        }
        return $this->normalizeWindowsObject($item, $base);
    }

    /**
     * Detect operational system family
     * 
     * @param string $item
     * 
     * @return string
     */
    private function detectSystemType(string $item): string
    {
        return preg_match(
            '/^[0-9]{2,4}-[0-9]{2}-[0-9]{2}/',
            $item
        ) ? self::SYSTEM_TYPE_WINDOWS : self::SYSTEM_TYPE_UNIX;
    }

    /**
     * Get the file type from the permissions.
     *
     * @param string $permissions
     *
     * @return string file type
     */
    protected function detectType($permissions)
    {
        return substr($permissions, 0, 1) === 'd' ? 'dir' : 'file';
    }

    /**
     * Only accurate to the minute (current year), or to the day.
     *
     * Inadequacies in timestamp accuracy are due to limitations of the FTP 'LIST' command
     *
     * Note: The 'MLSD' command is a machine-readable replacement for 'LIST'
     * but many FTP servers do not support it :(
     *
     * @param string $month      e.g. 'Aug'
     * @param string $day        e.g. '19'
     * @param string $timeOrYear e.g. '09:01' OR '2015'
     *
     * @return int
     */
    protected function normalizeUnixTimestamp($month, $day, $timeOrYear)
    {
        if (is_numeric($timeOrYear)) {
            $year = $timeOrYear;
            $hour = '00';
            $minute = '00';
            $seconds = '00';
        } else {
            $year = date('Y');
            list($hour, $minute) = explode(':', $timeOrYear);
            $seconds = '00';
        }
        $dateTime = DateTime::createFromFormat('Y-M-j-G:i:s', "{$year}-{$month}-{$day}-{$hour}:{$minute}:{$seconds}");
        return $dateTime->getTimestamp();
    }

    /**
     * Normalize list from UNIX format source
     * 
     * @param string $item
     * @param string $base
     * 
     * @return [type]
     */
    private function normalizeUnixObject(string $item, string $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 7);

        if (count(explode(' ', $item, 9)) !== 9) {
            throw new RuntimeException("Metadata can't be parsed from item '$item' , not enough parts.");
        }

        list($permissions, /* $number */, /* $owner */, /* $group */, $size, $month, $day, $timeOrYear, $name) = explode(' ', $item, 9);
        $type = $this->detectType($permissions);
        $path = $base === '' ? $name : $base . $this->separator . $name;

        if ($type === 'dir') {
            return compact('type', 'path');
        }

        $permissions = $this->normalizePermissions($permissions);
        $visibility = $permissions & 0044 ? 'public' : 'private';
        $size = (int) $size;

        $result = compact('type', 'path', 'visibility', 'size');
        $timestamp = $this->normalizeUnixTimestamp($month, $day, $timeOrYear);
        $result += compact('timestamp');
        return new FileAttributes($path, (int) $size, $visibility, $timestamp);
    }

    /**
     * Normalize list from Windows format source
     * 
     * @param string $item
     * @param string $base
     * 
     * @return [type]
     */
    private function normalizeWindowsObject(string $item, string $base)
    {
        echo "WIN\n";
        $item = preg_replace('#\s+#', ' ', trim($item), 3);

        if (count(explode(' ', $item, 4)) !== 4) {
            throw new RuntimeException("Metadata can't be parsed from item '$item' , not enough parts.");
        }

        list($date, $time, $size, $name) = explode(' ', $item, 4);
        $path = $base === '' ? $name : $base . $this->separator . $name;

        // Check for the correct date/time format
        $format = strlen($date) === 8 ? 'm-d-yH:iA' : 'Y-m-dH:i';
        $dt = DateTime::createFromFormat($format, $date . $time);
        $timestamp = $dt ? $dt->getTimestamp() : (int) strtotime("$date $time");

        if ($size === '<DIR>') {
            $type = 'dir';
            return compact('type', 'path', 'timestamp');
        }

        $type = 'file';
        $visibility = 'public';
        $size = (int) $size;

        return compact('type', 'path', 'visibility', 'size', 'timestamp');
    }

    /**
     * Check if one file exists
     * 
     * @param string $path
     * 
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        try {
            $this->fileSize($path);
            return true;
        } catch (UnableToRetrieveMetadata $exception) {
            return false;
        }
    }

    /**
     * Returns file size, if file exists
     * 
     * @param string $path
     * 
     * @return FileAttributes
     */
    public function fileSize(string $path): FileAttributes
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        $result = $this->getConnection()
            ->exec([
                CURLOPT_URL => "{$this->getBaseUri()}{$requestPath}",
                CURLOPT_NOBODY => true
            ]);
        if (!$result) {
            throw UnableToRetrieveMetadata::fileSize($path, '');
        }
        $headers = array_map(function ($x) {
            return array_map("trim", explode(":", $x, 2));
        }, array_filter(array_map("trim", explode("\n", $result))));
        if ($headers[0][0] != 'Content-Length' ||  $headers[0][1] < 0) {
            throw UnableToRetrieveMetadata::fileSize($path, error_get_last()['message'] ?? '');
        }
        return new FileAttributes($path, (int)$headers[0][1]);
    }

    /**
     * Write a new file.
     * 
     * @param mixed $path
     * @param mixed $contents
     * @param Config $config
     * 
     * @return void
     */
    public function write($path, $contents, Config $config): void
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);
        $this->writeStream($path, $stream, $config);
    }

    /**
     * Write a new file using a stream.
     *
     * @param  string  $path
     * @param  resource  $resource
     * @param  Config  $config  Config object
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config): void
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        $this->getConnection()
            ->exec([
                CURLOPT_URL => "{$this->getBaseUri()}{$requestPath}",
                CURLOPT_UPLOAD => 1,
                CURLOPT_INFILE => $resource,
            ]);
    }

    /**
     * Read a file.
     *
     * @param  string  $path
     * @return array|false
     */
    public function read($path): string
    {
        if (!$object = $this->readStream($path)) {
            return false;
        }
        $object['contents'] = stream_get_contents($object['stream']);
        fclose($object['stream']);
        unset($object['stream']);
        return (string)$object['contents'];
    }

    /**
     * Read a file as a stream.
     *
     * @param  string  $path
     * @return array|false
     */
    public function readStream($path)
    {
        $stream = fopen('php://temp', 'w+b');
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;

        $result = $this->getConnection()
            ->exec([
                CURLOPT_URL => "{$this->getBaseUri()}{$requestPath}",
                CURLOPT_FILE => $stream,
            ]);
        if (!$result) {
            fclose($stream);
            return false;
        }
        rewind($stream);
        if ($pathHasFolders) {
            $this->setConnectionRoot();
        }
        return ['type' => 'file', 'path' => $path, 'stream' => $stream];
    }

    /**
     * Copy a file.
     *
     * @param  string  $path
     * @param  string  $newpath
     * @return bool
     */
    public function copy(string $path, string  $newpath, ?Config $config = null): void
    {
        $file = $this->read($path);
        $this->write($newpath, $file, $config ?? new Config()) !== false;
    }

    /**
     * Move a file.
     *
     * @param  string  $path
     * @param  string  $newpath
     * @return bool
     */
    public function move(string $path, string  $newpath, ?Config $config = null): void
    {
        $moveCommands = [
            "RNFR {$path}",
            "RNTO {$newpath}",
        ];
        $this->rawPost($this->getConnection(), $moveCommands);
    }

    /**
     * Delete one file
     * 
     * @param string $path
     * 
     * @return void
     */
    public function delete(string $path): void
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        $this->rawCommand("DELE {$requestPath}");
    }

    /**
     * Delete one directory, but only if it is empty
     * 
     * @param string $path
     * 
     * @return void
     */
    public function deleteDirectory(string $path): void
    {
        $pathDir = pathinfo($path, PATHINFO_DIRNAME);
        $pathHasFolders = $pathDir !== '.';
        $requestPath = $pathHasFolders ? $this->applyPathPrefix($path) : $path;
        $this->rawCommand("RMD {$requestPath}");
    }

    /**
     * Get mime type. Not implemented
     * 
     * @param string $path
     * 
     * @return FileAttributes
     */
    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    /**
     * Geta last modification date. Not implemented
     * 
     * @param string $path
     * 
     * @return FileAttributes
     */
    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    /**
     * Sends an arbitrary command to an FTP server.
     *
     * @param  Curl  $connection  The CURL instance
     * @param  string  $command  The command to execute
     * @return array Returns the server's response as an array of strings
     */
    protected function rawCommand($command, bool $debug = false)
    {
        $response = '';
        $callback = static function ($ch, $string) use (&$response) {
            $response .= $string;
            return strlen($string);
        };
        $this->getConnection()->exec([
            CURLOPT_CUSTOMREQUEST => $command,
            CURLOPT_HEADERFUNCTION => $callback,
        ], $debug);
        return explode(PHP_EOL, trim($response));
    }

    /**
     * Sends an arbitrary command to an FTP server using POSTQUOTE option. This makes sure all commands are run
     * in succession and increases chance of success for complex operations like "move/rename file".
     *
     * @param  Curl  $connection  The CURL instance
     * @param  array  $commandsArray  The commands to execute
     * @return array Returns the server's response as an array of strings
     */
    protected function rawPost($connection, array $commandsArray)
    {
        $response = '';
        $callback = function ($ch, $string) use (&$response) {
            $response .= $string;
            return strlen($string);
        };
        $connection->exec([
            CURLOPT_POSTQUOTE => $commandsArray,
            CURLOPT_HEADERFUNCTION => $callback,
        ]);
        return explode(PHP_EOL, trim($response));
    }

    /**
     * Set the visibility for a file.
     *
     * @param  string  $path
     * @param  string  $visibility
     * @return array|false file meta data
     */
    public function setVisibility(string $path, string  $visibility): void
    {
        if ($visibility === 'public') {
            $mode = $this->getPermPublic();
        } else {
            $mode = $this->getPermPrivate();
        }
        $request = sprintf('SITE CHMOD %o %s', $mode, $path);
        $this->rawCommand($request);
    }

    /**
     * Returns the base url of the connection.
     *
     * @return string
     */
    protected function getBaseUri()
    {
        $protocol = boolval($this->getConfig('ssl')) ? 'ftps' : 'ftp';
        return $protocol . '://' . $this->getConfig('host') . ':' . $this->getConfig('port');
    }

    /**
     * Returns the base url of the connection.
     *
     * @return string
     */
    protected function getConfig(string $field, $default = null)
    {
        return  Arrays::get($this->config, $field, $default);
    }

    /**
     * Get CURL connection handler
     * 
     * @return [type]
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the public permission value.
     *
     * @return int
     */
    public function getPermPublic()
    {
        return $this->permPublic;
    }

    /**
     * Get the private permission value.
     *
     * @return int
     */
    public function getPermPrivate()
    {
        return $this->permPrivate;
    }

    /**
     * Get visibility of file. Not implemented
     * 
     * @param string $path
     * 
     * @return FileAttributes
     */
    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path);
    }

    /**
     * Normalize a permissions string.
     *
     * @param  string  $permissions
     * @return int
     */
    protected function normalizePermissions($permissions)
    {
        // remove the type identifier
        $permissions = substr($permissions, 1);
        // map the string rights to the numeric counterparts
        $map = ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1'];
        $permissions = strtr($permissions, $map);
        // split up the permission groups
        $parts = str_split($permissions, 3);
        // convert the groups
        $mapper = function ($part) {
            return array_sum(str_split($part));
        };
        // converts to decimal number
        return octdec(implode('', array_map($mapper, $parts)));
    }
}
