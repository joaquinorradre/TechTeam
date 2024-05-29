<?php

namespace Tests\Unit\Serializers;

use App\Serializers\UsersFollowSerializer;
use PHPUnit\Framework\TestCase;

class UsersFollowSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serializationWithValidData()
    {
        $serializer = new UsersFollowSerializer();

        $userData = [
            ['username' => 'user1', 'streamerLogins' => ['streamer1', 'streamer2']],
            ['username' => 'user2', 'streamerLogins' => ['streamer3']]
        ];

        $serializedData = $serializer->serialize($userData);

        $this->assertCount(2, $serializedData);
        $this->assertEquals('user1', $serializedData[0]['username']);
        $this->assertEquals(['streamer1', 'streamer2'], $serializedData[0]['followedStreamers']);
        $this->assertEquals('user2', $serializedData[1]['username']);
        $this->assertEquals(['streamer3'], $serializedData[1]['followedStreamers']);
    }
}
