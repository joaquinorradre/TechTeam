<?php

namespace DeleteStreamer;

use App\Http\Requests\DeleteStreamerRequest;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Mockery;

class DeleteStreamerRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function should_authorize_request()
    {
        $request = new DeleteStreamerRequest();

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function should_return_correct_validation_rules()
    {
        $request = new DeleteStreamerRequest();

        $rules = $request->rules();

        $this->assertEquals([
            'userId' => 'required|string',
            'streamerId' => 'required|string',
        ], $rules);
    }

    /**
     * @test
     */
    public function should_return_correct_validation_messages()
    {
        $request = new DeleteStreamerRequest();

        $messages = $request->messages();

        $this->assertEquals([
            'userId.required' => 'El ID de usuario es obligatorio',
            'userId.string' => 'El ID del usuario debe ser una cadena de caracteres.',
            'streamerId.required' => 'La ID del streamer es obligatoria.',
            'streamerId.string' => 'La ID del streamer debe ser una cadena de caracteres.',
        ], $messages);
    }

    /**
     * @test
     */
    public function when_data_is_valid_should_pass_validation()
    {
        $data = [
            'userId' => 'validUserId',
            'streamerId' => 'validStreamerId',
        ];

        $request = new DeleteStreamerRequest();

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function when_data_is_invalid_should_fail_validation()
    {
        $data = [
            'userId' => 12,
            'streamerId' => '',
        ];
        $request = new DeleteStreamerRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors();
        $this->assertEquals('El ID del usuario debe ser una cadena de caracteres.', $errors->first('userId'));
        $this->assertEquals('La ID del streamer es obligatoria.', $errors->first('streamerId'));
    }
}