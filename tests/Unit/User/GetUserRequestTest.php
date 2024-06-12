<?php

namespace Tests\Unit\User;

use App\Http\Requests\GetUsersRequest;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;

class GetUserRequestTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new GetUsersRequest();
    }

    /**
     * @test
     */
    public function given_a_request_return_true_authorization()
    {
        $this->assertTrue($this->request->authorize());
    }

    /**
     * @test
     */
    public function given_an_empty_id_return_error()
    {
        $validator = Validator::make(['id' => null], $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /**
     * @test
     */
    public function given_an_id_with_invalid_format_return_error()
    {
        $validator = Validator::make(['id' => 'abc'], $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /**
     * @test
     */
    public function given_a_valid_id_return_value()
    {
        $validator = Validator::make(['id' => 123], $this->request->rules());

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->errors()->has('id'));
    }
}
