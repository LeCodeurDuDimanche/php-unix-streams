<?php

namespace lecodeurdudimanche\UnixStream;

class Message {
    protected $type, $data;

    public function __construct(int $type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType() : int
    {
        return $this->type;
    }

    public function is(int $type) : bool
    {
        return $this->type === $type;
    }

    public function getData()
    {
        return $this->data;
    }

}
