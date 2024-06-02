<?php


namespace Tests\Unit;

use App\Serializers\UserListSerializer;
use PHPUnit\Framework\TestCase;

class UserDataSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serializationWithValidData()
    {
        $serializer = new UserListSerializer();

        $users = [
            ['id' => 'id', 'login' => 'login', 'display_name' => 'display_name', 'type' => 'type', 'broadcaster_type' => 'broadcaster_type', 'description' => 'description', 'profile_image_url' => 'profile_image_url', 'offline_image_url' => 'offline_image_url','view_count' => 'view_count','created_at' => 'created_at',],
        ];

        $serializedUsers = $serializer->serialize($users);

        $this->assertCount(1, $serializedUsers);
        $this->assertArrayHasKey('id', $serializedUsers[0]);
        $this->assertEquals('display_name', $serializedUsers[0]['display_name']);
        $this->assertEquals('id', $serializedUsers[0]['id']);
        $this->assertEquals('broadcaster_type', $serializedUsers[0]['broadcaster_type']);
        $this->assertEquals('description', $serializedUsers[0]['description']);
        $this->assertEquals('profile_image_url', $serializedUsers[0]['profile_image_url']);
        $this->assertEquals('created_at', $serializedUsers[0]['created_at']);
        $this->assertEquals('view_count', $serializedUsers[0]['view_count']);
    }
}