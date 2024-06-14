<?php

namespace Streams;


use App\Http\Requests\GetStreamsRequest;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Mockery;

class GetStreamsRequestTest extends TestCase
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
        $request = new GetStreamsRequest();

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function should_return_empty_validation_rules()
    {
        $request = new GetStreamsRequest();
        $rules = $request->rules();
        $this->assertEquals([], $rules);
    }

    /**
     * @test
     */
    public function should_fail_when_parameters_are_present()
    {
        $data = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $request = new GetStreamsRequest();
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->passes());
        $errors = $validator->errors();
        $this->assertEquals('No se permiten parámetros en esta solicitud.', $errors->first('parameters'));
    }

    /**
     * @test
     */
    public function should_pass_when_no_parameters_are_present()
    {
        $data = [];

        $request = new GetStreamsRequest();
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->passes());
    }

}