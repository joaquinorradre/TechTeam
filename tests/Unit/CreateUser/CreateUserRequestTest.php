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
    }

    public function testAuthorization()
    {
        $request = new CreateUserRequest();

        $this->assertTrue($request->authorize());
    }

    public function testValidationRules()
    {
        $request = new CreateUserRequest();

        $rules = $request->rules();

        $this->assertEquals([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:3',
        ], $rules);
    }

    public function testValidationMessages()
    {
        $request = new CreateUserRequest();

        $messages = $request->messages();

        $this->assertEquals([
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.string' => 'El nombre de usuario debe ser una cadena de caracteres.',
            'username.max' => 'El nombre de usuario no puede tener más de 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser una cadena de caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ], $messages);
    }

    public function testValidationSuccess()
    {
        $data = [
            'username' => 'validusername',
            'password' => 'validpassword',
        ];

        $request = new CreateUserRequest();

        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
    }

    public function testValidationFailure()
    {
        $data = [
            'username' => '',
            'password' => '12',
        ];
        $request = new CreateUserRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $errors = $validator->errors();
        $this->assertEquals('El nombre de usuario es obligatorio.', $errors->first('username'));
        $this->assertEquals('La contraseña debe tener al menos 8 caracteres.', $errors->first('password'));
    }
}
