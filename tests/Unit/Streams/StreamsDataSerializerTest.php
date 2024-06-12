<?php

namespace Tests\Unit\Streams;

use App\Serializers\StreamsDataSerializer;
use Mockery;
use PHPUnit\Framework\TestCase;

class StreamsDataSerializerTest extends TestCase
{
    protected StreamsDataSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new StreamsDataSerializer();
    }

    /**
     * @test
     */
    public function given_valid_data_serialization_should_be_successful()
    {
        $streams = [
            ['title' => 'Stream 1', 'user_name' => 'User 1'],
            ['title' => 'Stream 2', 'user_name' => 'User 2'],
        ];

        $serializedStreams = $this->serializer->serialize($streams);

        $this->assertCount(2, $serializedStreams);
        $this->assertArrayHasKey('title', $serializedStreams[0]);
        $this->assertArrayHasKey('user_name', $serializedStreams[0]);
        $this->assertEquals('Stream 1', $serializedStreams[0]['title']);
        $this->assertEquals('User 1', $serializedStreams[0]['user_name']);
    }

    /**
     * @test
     */
    public function given_empty_data_serialization_should_return_empty_array()
    {
        $streams = [];

        $serializedStreams = $this->serializer->serialize($streams);

        $this->assertIsArray($serializedStreams);
        $this->assertEmpty($serializedStreams);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
