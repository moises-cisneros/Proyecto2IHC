<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Los estilos se compilan con Vite; los tests no dependen del build.
        $this->withoutVite();
    }
}
