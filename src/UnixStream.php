<?php

namespace lecodeurdudimanche\UnixStream;

use lecodeurdudimanche\UnixStream\IOException;

class UnixStream {


    public const MODE_PEAK = 0;
    public const MODE_READ = 1;
    public const MODE_DISCARD = 2;

    protected $handle;
    protected $isServer;
    protected $serializer;
    protected $buffer;

    public function __construct(?string $file = null, ?MessageSerializer $serializer = null)
    {
        if ($file)
        {
            $this->handle = @stream_socket_client("unix://$file", $errno, $errstr, 5000);
            if (! $this->handle)
                throw new IOException("Cannot connect the socket to file $file : $errstr ($errno)");
        }
        $this->serializer = $serializer ?? new JSONMessageSerializer;
        $this->buffer = new FIFOBuffer();
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

    private function readFromStream() : ?Message
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

    public function read(int $mode = MODE_READ) : ?Message
    {
        if (! $this->buffer->isEmpty()) {
            return $mode == MODE_READ ? $this->buffer->dequeue() : $this->buffer->peak();
        }

        $message = $this->readFromStream();
        if ($mode == MODE_PEAK)
            $this->buffer->queue($message);
        return $message;
    }

    public function readNext(array $acceptedTypes, bool $wait = true, int $mode = MODE_DISCARD) : ?Message
    {
        for ($i = 0; $i < $this->buffer->length(); $i++)
        {
            $message = $mode == MODE_DISCARD ? $this->buffer->dequeue() : $this->buffer->get($i);
            if ($message && in_array($message->getType(), $acceptedTypes)) {
                if ($mode == MODE_READ) $this->buffer->remove($i);
                return $message;
            }
        }

        while ($wait || $this->hasData())
        {
            while ($wait && !$this->waitData(50000)) ;

            $message = $this->readFromStream();

            if ($message && in_array($message->getType(), $acceptedTypes))
                return $message;
            else if ($mode == MODE_READ) {
                $this->buffer->queue($message);
            }
        }
        return null;
    }

    public function addToBuffer(Message $m) : void
    {
        $this->buffer->queue($m);
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
