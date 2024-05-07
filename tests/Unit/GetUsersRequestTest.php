<?php

namespace Tests\Unit;

use App\Http\Requests\GetUsersRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetUsersRequestTest extends TestCase
{
    /**
     * Test authorization.
     *
     * @return void
     */
    public function testAuthorization()
    {
        $request = new GetUsersRequest();

        $this->assertFalse($request->authorize());
    }

    /**
     * Test validation rules.
     *
     * @return void
     */
    public function testValidationRules()
    {
        $request = new GetUsersRequest();

        $validator = Validator::make(['id' => 1], $request->rules());
        $this->assertTrue($validator->passes());

        $validator = Validator::make(['id' => 'string'], $request->rules());
        $this->assertFalse($validator->passes());
    }
}
