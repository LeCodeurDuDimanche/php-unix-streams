<?php

namespace lecodeurdudimanche\UnixStream;

class JSONMessageSerializer implements MessageSerializer {

    public function fromJSON(string $data) : Message
    {
        $data = json_decode($data);

        if (!$data || !array_key_exists('type', $data) || ! array_key_exists('data', $data) || ! is_integer($data['type']))
            return null;

        return new Message($data['type'], $data['data']);
    }

    public function toJSON(Message $message) : string
    {
        return json_encode(['type' => $message->getType(), 'data' => $message->getData()]);
    }
}
