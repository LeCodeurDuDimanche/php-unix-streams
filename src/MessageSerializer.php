<?php

namespace lecodeurdudimanche\UnixStream;

interface MessageSerializer {

    public function fromJSON(string $data) : ?Message;
    public function toJSON(Message $message) : string;
}
