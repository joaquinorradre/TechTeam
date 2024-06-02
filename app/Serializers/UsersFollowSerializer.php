<?php

namespace App\Serializers;

class UsersFollowSerializer
{
    public static function serialize(array $data): array
    {
        $serializedData = [];

        foreach ($data as $username => $streamers) {
            $serializedData[] = [
                'username' => $username,
                'followedStreamers' => !empty($streamers) ? $streamers : []
            ];
        }
        return $serializedData;
    }
}
