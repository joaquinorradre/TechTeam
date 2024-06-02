<?php

namespace App\Serializers;

class UsersFollowSerializer
{
    public static function serialize(array $userData): array
    {
        $serializedData = [];

        foreach ($userData as $user) {
            $serializedData[] = [
                'username' => $user['username'],
                'followedStreamers' => $user['streamerLogins'],
            ];
        }

        return $serializedData;
    }
}