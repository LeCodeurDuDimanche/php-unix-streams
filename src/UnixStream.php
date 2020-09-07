<?php

namespace lecodeurdudimanche\UnixStream;

use lecodeurdudimanche\UnixStream\IOException;

class UnixStream {

    protected $handle;
    protected $isServer;
    protected $serializer;

    public function __construct(?string $file = null, ?MessageSerializer $serializer = null)
    {
        if ($file)
        {
            $this->handle = @stream_socket_client("unix://$file", $errno, $errstr, 5000);
            if (! $this->handle)
                throw new IOException("Cannot connect the socket to file $file : $errstr ($errno)");
        }
        $this->serializer = $serializer ?? new JSONMessageSerializer;
    }

    public static function fromExistingSocket($socket)
    {
        $stream = new UnixStream;
        $stream->handle = $socket;
        return $stream;
    }

    //TODO: check for IO errors
    public function write(Message $message) : void
    {
        $data = $this->serializer->toJSON($message);
        fprintf($this->handle, "%d\n%s", strlen($data), $data);
    }

    public function read() : ?Message
    {
        $read = fscanf($this->handle, "%d", $size); // Consumes line end character
        if ($read != 1)
        {
            if ($read === false)
                throw new IOException("Broken pipe");
            return null;
        }

        $data = fread($this->handle, $size);
        return $this->serializer->fromJSON($data);
    }

    public function readNext(array $acceptedTypes, bool $wait = true) : ?Message
    {
        while ($wait || $this->hasData())
        {
            while ($wait && !$this->waitData(50000)) ;

            $message = $this->read();
            if ($message && in_array($message->getType(), $acceptedTypes))
                return $message;
        }
        return null;
    }

    public function hasData(): bool
    {
        return $this->waitData(0);
    }

    public function waitData(int $microseconds): bool
    {
        $arr = [$this->handle];
        return stream_select($arr, $thisIsANonExistingVariable, $absolutelyNotAnUglyWayToIgnoreThisParameter, 0, $microseconds);
    }

    public function close() : void
    {
        fclose($this->handle);
        $this->handle = null;
    }
}
