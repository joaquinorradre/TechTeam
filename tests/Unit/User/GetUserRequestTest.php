<?php

namespace Tests\Unit;

use App\Http\Requests\GetUsersRequest;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;

class GetUserRequestTest extends TestCase
{
    /**
     * @test
     */
    public function givenARequestReturnTrueAuthorization()
    {
        $request = new GetUsersRequest();

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function givenAnEmptyIdReturnError()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => null], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /**
     * @test
     */
    public function givenAnIdWithInvalidFormatReturnError()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 'abc'], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /**
     * @test
     */
    public function givenAValidIdReturnValue()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 123], $request->rules());

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->errors()->has('id'));
    }

}
