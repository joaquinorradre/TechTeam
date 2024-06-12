<?php

namespace Tests\Unit\CreateUser;

use App\Http\Requests\CreateUserRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateUserRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new CreateUserRequest();
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
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:3',
        ], $rules);
    }

    /**
     * @test
     */
    public function should_return_correct_validation_messages()
    {
        $messages = $this->request->messages();

        $this->assertEquals([
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.string' => 'El nombre de usuario debe ser una cadena de caracteres.',
            'username.max' => 'El nombre de usuario no puede tener más de 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ], $messages);
    }

    /**
     * @test
     */
    public function when_data_is_valid_should_pass_validation()
    {
        $data = [
            'username' => 'validusername',
            'password' => 'validpassword',
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
            'username' => '',
            'password' => '12',
        ];
        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors();
        $this->assertEquals('El nombre de usuario es obligatorio.', $errors->first('username'));
        $this->assertEquals('La contraseña debe tener al menos 8 caracteres.', $errors->first('password'));
    }
}