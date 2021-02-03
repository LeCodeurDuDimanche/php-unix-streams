<?php

namespace lecodeurdudimanche\UnixStream;

//TODO: do a circular array for performance
class FIFOBuffer {
    private $array;

    public function __construct()
    {
        $this->array = array();
    }

    public function dequeue() : ?Message
    {
        if ($this->isEmpty())
            return null;

        return array_shift($this->array);
    }

    public function queue(Message $message) : void
    {
        array_push($this->array, $message);
    }

    public function length() : int
    {
        return count($this->array);
    }

    public function isEmpty() : bool
    {
        return $this->length() != 0;
    }

}
