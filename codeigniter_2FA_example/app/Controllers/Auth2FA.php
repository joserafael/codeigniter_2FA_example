<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use OTPHP\TOTP;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Auth2FA extends BaseController
{
    use ResponseTrait;

    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Login for API
     * POST /auth/login
     */
    public function login()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        // Check user credentials
        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->failUnauthorized('Invalid email or password');
        }

        $responseData = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'is_2fa_enabled' => (bool)$user['is_2fa_enabled'],
            'is_2fa_initialized' => !empty($user['secret_2fa']),
        ];

        if ($user['is_2fa_enabled']) {
            $responseData['message'] = '2FA required. Please call /auth/verify with your code.';
            // In a real app, return a temporary token here that only allows access to /verify
        } else {
            $responseData['message'] = 'Login successful (No 2FA)';
        }

        return $this->respond($responseData);
    }

    /**
     * Generate Secret and QR Code
     * POST /auth/setup
     */
    public function setup()
    {
        // For this example, we accept user_id. In production, get from session/token
        $userId = $this->request->getVar('user_id'); 
        if(!$userId) return $this->fail('User ID required for setup demo', 400);

        $user = $this->userModel->find($userId);
        if (!$user) return $this->failNotFound('User not found');

        // Generate new secret
        $totp = TOTP::create();
        $totp->setLabel($user['email']);
        $totp->setIssuer('CI4-2FA-Demo');
        $secret = $totp->getSecret();

        // Save secret to DB (not enabled yet, waiting for verification)
        $this->userModel->update($userId, ['secret_2fa' => $secret]);

        // Generate QR Code SVG
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($totp->getProvisioningUri());

        return $this->respond([
            'secret' => $secret, // Display manual entry key
            'qr_code_svg' => base64_encode($qrCodeSvg),
            'message' => 'Scan this QR code with Authenticator App or use the secret key.',
        ]);
    }

    /**
     * Verify OTP and Enable 2FA / Complete Login
     * POST /auth/verify
     */
    public function verify()
    {
        $userId = $this->request->getVar('user_id');
        $code = $this->request->getVar('code'); // OTP code

        if(!$userId || !$code) return $this->fail('User ID and Code required', 400);

        $user = $this->userModel->find($userId);
        if (!$user) return $this->failNotFound('User not found');

        $secret = $user['secret_2fa'];
        if (!$secret) return $this->fail('2FA not initialized for this user. Call /setup first.');

        $totp = TOTP::create($secret);
        
        if ($totp->verify($code)) {
            // Enable 2FA if checking for setup
            if (!$user['is_2fa_enabled']) {
                $this->userModel->update($userId, ['is_2fa_enabled' => 1]);
                return $this->respond([
                    'status' => 'success',
                    'message' => '2FA Verified and Enabled! Login complete.',
                ]);
            }
            return $this->respond([
                'status' => 'success',
                'message' => '2FA Verified! Login complete.',
            ]);
        }

        return $this->failUnauthorized('Invalid 2FA Code');
    }
}
