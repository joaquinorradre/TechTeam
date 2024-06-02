<?php

namespace Tests\Unit\User;

use App\Http\Requests\GetUsersRequest;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;

class GetUserRequestTest extends TestCase
{
    /**
     * @test
     */
    public function given_a_request_return_true_authorization()
    {
        $request = new GetUsersRequest();

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function given_an_empty_id_return_error()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => null], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /**
     * @test
     */
    public function given_an_id_with_invalid_format_return_error()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 'abc'], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /**
     * @test
     */
    public function given_a_valid_id_return_value()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 123], $request->rules());

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->errors()->has('id'));
    }
}