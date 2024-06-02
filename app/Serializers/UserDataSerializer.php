<?php

namespace App\Serializers;

class UserDataSerializer
{
    public static function serialize(array $data): array
    {
        $serializedData = [];

        foreach ($data as $username => $streamers) {
            $serializedData[] = [
                'username' => $username,
                'followedStreamers' => $streamers
            ];
        }

        return $serializedData;
    }

    public static function serializeTimeline(array $streams): array
    {
        return array_map(function ($stream) {
            return [
                'streamerId' => $stream['streamerId'],
                'streamerName' => $stream['streamerName'],
                'title' => $stream['title'],
                'game' => $stream['game'],
                'viewerCount' => $stream['viewerCount'],
                'startedAt' => $stream['startedAt'],
            ];
        }, $streams);
    }
}
