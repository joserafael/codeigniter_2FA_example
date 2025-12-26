<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class HomeTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testHomeIndex()
    {
        $result = $this->get('/');

        $result->assertStatus(200);
        $result->assertSee('Welcome to CodeIgniter');
    }
}
