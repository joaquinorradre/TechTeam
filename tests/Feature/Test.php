<?php

namespace Tests\Unit\Services;

use App\Clients\ApiClient;
use App\Services\StreamsDataManager;
use Mockery;
use Tests\TestCase;
class Test extends TestCase
{
    public function testGetUserData()
    {
        // Mock de ApiClient
        $apiClient = Mockery::mock(ApiClient::class);

        $getTokenExpectedResponse = json_encode([
            'access_token' => 'c5eigbsstxhl447v8qd299v5qzn2qs',
            'expires_in' => 5092434,
            'token_type' => 'bearer'
        ]);

        $getStreamsExpectedResponse = json_encode(['data' => [[
            'title' => 'Stream title',
            'user_name' => 'user_name',
        ]]]);

        // Configuramos el comportamiento esperado del mock
        $apiClient
            ->expects('getToken')
            ->with('https://id.twitch.tv/oauth2/token')
            ->once()
            ->andReturn($getTokenExpectedResponse);

        $apiClient
            ->expects('makeCurlCall')
            ->with('https://api.twitch.tv/helix/streams',"c5eigbsstxhl447v8qd299v5qzn2qs")
            ->once()
            ->andReturn($getStreamsExpectedResponse);


        // Creamos una instancia de UserDataManager con el mock de ApiClient
        $StreamsDataManager = new StreamsDataManager($apiClient);

        // Ejecutamos el método que queremos probar
        $result = $StreamsDataManager->getStreams('https://api.twitch.tv/helix/streams');

        // Verificamos que el resultado sea un token válido
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $response = $this->get('/analytics/streams');
        $response->assertContent('[{"title":"Stream title","user_name":"user_name"}]');
    }
}

