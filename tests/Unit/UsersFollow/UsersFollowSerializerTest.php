<?php

namespace Tests\Unit\UsersFollow;

use App\Serializers\UsersFollowSerializer;
use Tests\TestCase;

class UsersFollowSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function should_serialize_users_follow_data_correctly()
    {
        $data = [
            'user1' => ['streamer1', 'streamer2'],
            'user2' => ['streamer3'],
        ];
        $expected = [
            [
                'username' => 'user1',
                'followedStreamers' => ['streamer1', 'streamer2'],
            ],
            [
                'username' => 'user2',
                'followedStreamers' => ['streamer3'],
            ],
        ];

        $result = UsersFollowSerializer::serialize($data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function should_return_empty_followed_streamers_for_users_with_no_followed_streamers()
    {
        $data = [
            'user1' => [],
            'user2' => ['streamer3'],
        ];
        $expected = [
            [
                'username' => 'user1',
                'followedStreamers' => [],
            ],
            [
                'username' => 'user2',
                'followedStreamers' => ['streamer3'],
            ],
        ];

        $result = UsersFollowSerializer::serialize($data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function should_return_empty_array_when_input_is_empty()
    {
        $data = [];

        $result = UsersFollowSerializer::serialize($data);

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }
}
