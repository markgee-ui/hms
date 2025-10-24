<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
    public function loginAsAdmin()
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
    }
}
