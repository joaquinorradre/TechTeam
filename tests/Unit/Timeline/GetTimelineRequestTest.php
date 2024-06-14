<?php

namespace Tests\Unit\Timeline;

use App\Http\Requests\GetTimelineRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTimelineRequestTest extends TestCase
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
        $request = new GetTimelineRequest();

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function should_return_correct_validation_rules()
    {
        $request = new GetTimelineRequest();

        $rules = $request->rules();

        $this->assertEquals([
            'userId' => 'required|string',
        ], $rules);
    }

    /**
     * @test
     */
    public function when_data_is_valid_should_pass_validation()
    {
        $data = [
            'userId' => 'validUserId',
        ];
        $request = new GetTimelineRequest();

        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function when_data_is_invalid_should_fail_validation()
    {
        $data = [
            'userId' => '',
        ];
        $request = new GetTimelineRequest();

        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertEquals('The user id field is required.', $errors->first('userId'));
    }
}
