<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $envPath = dirname(__DIR__).DIRECTORY_SEPARATOR.'.env';
        $created = false;
        if (! file_exists($envPath)) {
            touch($envPath);
            $created = true;
        }

        $app = parent::createApplication();

        if ($created && file_exists($envPath) && filesize($envPath) === 0) {
            @unlink($envPath);
        }

        return $app;
    }
}
