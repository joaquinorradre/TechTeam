<?php

namespace Tests\Unit\Timeline;

use App\Http\Requests\GetTimelineRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class GetTimelineRequestTest extends TestCase
{
    protected GetTimelineRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new GetTimelineRequest();
    }

    /**
     * @test
     */
    public function should_authorize_request()
    {
        $this->assertTrue($this->request->authorize());
    }

    /**
     * @test
     */
    public function should_return_correct_validation_rules()
    {
        $rules = $this->request->rules();

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

        $validator = Validator::make($data, $this->request->rules());

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

        $validator = Validator::make($data, $this->request->rules());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertEquals('The user id field is required.', $errors->first('userId'));
    }

}

