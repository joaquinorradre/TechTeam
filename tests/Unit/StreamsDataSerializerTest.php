<?php

namespace Tests\Unit;

use App\Serializers\StreamsDataSerializer;
use PHPUnit\Framework\TestCase;

class StreamsDataSerializerTest extends TestCase
{
    public function testSerialize()
    {
        $serializer = new StreamsDataSerializer();

        $streams = [
            ['title' => 'Stream 1', 'user_name' => 'User 1'],
            ['title' => 'Stream 2', 'user_name' => 'User 2'],
        ];

        $serializedStreams = $serializer->serialize($streams);

        $this->assertCount(2, $serializedStreams);
        $this->assertArrayHasKey('title', $serializedStreams[0]);
        $this->assertArrayHasKey('user_name', $serializedStreams[0]);
        $this->assertEquals('Stream 1', $serializedStreams[0]['title']);
        $this->assertEquals('User 1', $serializedStreams[0]['user_name']);
    }
}

