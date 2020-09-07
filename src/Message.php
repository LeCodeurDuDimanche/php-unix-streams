<?php

namespace lecodeurdudimanche\UnixStream;

abstract class Message {
    protected $type, $data;

    public function __construct(int $type, $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public abstract static function fromJSON(string $data);
    public abstract function toJSON();

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
