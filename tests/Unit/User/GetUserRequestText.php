<?php

namespace Tests\Unit;

use App\Http\Requests\GetUsersRequest;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Validator;

class GetUserRequestText extends TestCase
{
    /** @test */
    public function it_authorizes_the_request()
    {
        $request = new GetUsersRequest();

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function it_validates_required_numeric_id()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => null], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /** @test */
    public function it_validates_numeric_id()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 'abc'], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('id'));
    }

    /** @test */
    public function it_passes_validation_with_valid_id()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 123], $request->rules());

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->errors()->has('id'));
    }

}
