<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;

class AuthTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    public function testLoginSuccessNo2FA()
    {
        // Create user
        $userModel = new UserModel();
        $userModel->insert([
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'is_2fa_enabled' => 0
        ]);

        $result = $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $result->assertStatus(200);
        $result->assertJSONFragment(['message' => 'Login successful (No 2FA)']);
    }

    public function testSetup2FA()
    {
        $userModel = new UserModel();
        $id = $userModel->insert([
            'email' => 'test2@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'is_2fa_enabled' => 0
        ]);

        $result = $this->post('/auth/setup', [
            'user_id' => $id
        ]);

        $result->assertStatus(200);
        $result->assertSee('secret');
        $result->assertSee('qr_code_svg');
    }
}
