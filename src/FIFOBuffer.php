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

    public function peak() : ?Message
    {
        if ($this->isEmpty())
            return null;
        return $this->array[0];
    }

    public function get(int $index) : ?Message
    {
        if ($index < 0 || $index >= $this->length())
            return null;
        return $this->array[$index];
    }

    public function remove(int $index) : bool
    {
        if ($index < 0 || $index >= $this->length())
            return false;
        array_splice($this->array, $index, 1);
        return true;
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
