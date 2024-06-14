<?php

namespace Tests\Unit\UsersFollow;

use App\Http\Requests\GetUsersFollowRequest;
use Tests\TestCase;

class GetUsersFollowRequestTest extends TestCase
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
        $request = new GetUsersFollowRequest();

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function should_return_empty_validation_rules()
    {
        $request = new GetUsersFollowRequest();
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

        $request = new GetUsersFollowRequest();
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

        $request = new GetUsersFollowRequest();
        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->passes());
    }
}