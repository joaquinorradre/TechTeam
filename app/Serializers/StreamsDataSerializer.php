<?php

namespace App\Serializers;

class StreamsDataSerializer
{
    public function serialize(array $streams): array
    {
        $serializedStreams = [];

        foreach ($streams as $stream) {
            $serializedStreams[] = [
                'title' => $stream['title'],
                'user_name' => $stream['user_name'],
            ];
        }

        return $serializedStreams;
    }
}
