<?php

namespace Tests\Unit\Timeline;

use App\Serializers\TimelineDataSerializer;
use Tests\TestCase;

class GetTimelineSerializerTest extends TestCase
{
    protected TimelineDataSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = new TimelineDataSerializer();
    }

    /**
     * @test
     */
    public function should_serialize_timeline_data_correctly()
    {
        $timelineData = [
            [
                'user_id' => '123',
                'user_name' => 'Streamer1',
                'title' => 'Stream Title 1',
                'game_name' => 'Game 1',
                'viewer_count' => 100,
                'started_at' => '2023-06-01T12:34:56Z',
            ],
            [
                'user_id' => '456',
                'user_name' => 'Streamer2',
                'title' => 'Stream Title 2',
                'game_name' => 'Game 2',
                'viewer_count' => 200,
                'started_at' => '2023-06-01T13:34:56Z',
            ],
        ];

        $expected = [
            [
                'streamerId' => '123',
                'streamerName' => 'Streamer1',
                'title' => 'Stream Title 1',
                'game' => 'Game 1',
                'viewerCount' => 100,
                'startedAt' => '2023-06-01T12:34:56Z',
            ],
            [
                'streamerId' => '456',
                'streamerName' => 'Streamer2',
                'title' => 'Stream Title 2',
                'game' => 'Game 2',
                'viewerCount' => 200,
                'startedAt' => '2023-06-01T13:34:56Z',
            ],
        ];

        $result = $this->serializer->serialize($timelineData);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function should_return_empty_array_when_timeline_data_is_empty()
    {
        $timelineData = [];

        $result = $this->serializer->serialize($timelineData);

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }
}
