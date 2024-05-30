<?php

namespace Tests\Feature;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class CreateUserRequestTest extends TestCase
{



    /**
     * @Test
     */
    public function givenNoUsernameReturnsError()
    {
        $data = ['password' => 'password123'];

        $validator = Validator::make($data, (new CreateUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
    }

    /**
     * @Test
     */
    public function givenNoPasswordreturnsError()
    {
        $data = ['username' => 'testuser'];

        $validator = Validator::make($data, (new CreateUserRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * @Test
     */
    public function givenCorrectInformationReturnsTrue()
    {
        $data = [
            'username' => 'testuser',
            'password' => 'password123'
        ];

        $validator = Validator::make($data, (new CreateUserRequest())->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * @Test
     */
    public function givenTooShortPasswordReturnsErrorMessage()
    {
        $data = [
            'username' => 'testuser',
            'password' => 'short'
        ];

        $validator = Validator::make($data, (new CreateUserRequest())->rules(), (new CreateUserRequest())->messages());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('password', $errors);
        $this->assertEquals('La contraseña debe tener al menos 8 caracteres.', $errors['password'][0]);
    }

    /**
     * @Test
     */
    public function givenTooLongUsernameReturnsErrorMessage()
    {
        $data = [
            'username' => str_repeat('a', 256),
            'password' => 'password123'
        ];

        $validator = Validator::make($data, (new CreateUserRequest())->rules(), (new CreateUserRequest())->messages());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('username', $errors);
        $this->assertEquals('El nombre de usuario no puede tener más de 255 caracteres.', $errors['username'][0]);
    }
}
