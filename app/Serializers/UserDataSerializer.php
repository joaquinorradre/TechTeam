<?php

namespace App\Serializers;

class UserDataSerializer
{
    public function serialize(array $userData): array
    {

        $serializedUserData = [];
        foreach ($userData as $user) {
            $userData = [];
            foreach ($user as $key => $value) {
                $userData[$key] = $value;
            }
            $serializedUserData[] = $userData;
        }

        return $serializedUserData;
    }

}