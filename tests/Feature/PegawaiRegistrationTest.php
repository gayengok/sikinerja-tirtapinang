<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_and_role_specific_login_routes_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
        $this->get('/atasan/register')->assertNotFound();
        $this->post('/atasan/register')->assertNotFound();
        $this->get('/atasan/login')->assertNotFound();
        $this->get('/kepala-bagian/login')->assertNotFound();
        $this->get('/direktur/login')->assertNotFound();
        $this->get('/master/login')->assertNotFound();
    }
}
