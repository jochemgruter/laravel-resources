<?php
namespace Gruter\ResourceViewer\Tests;

use Gruter\ResourceViewer\Facades\Resource;
use Illuminate\Contracts\Auth\Authenticatable;
use Mockery;
use Orchestra\Testbench\TestCase;

class ResourceTest extends TestCase
{
    protected function setUp(): void
    {

        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--realpath' => realpath(__DIR__.'/Migrations'),
        ]);

        $this->withFactories(__DIR__.'/Factories');

        $this->authenticate();
    }

    protected function authenticate()
    {
        $this->actingAs(Mockery::mock(Authenticatable::class));
    }

    protected function getPackageProviders($app)
    {
        return [
            TestResourceServiceProvider::class
        ];
    }


    protected function getPackageAliases($app)
    {
        return [
            'Resource' => Resource::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
        $app['config']->set('resource-viewer.route', '/testing');
        $app['config']->set('app.key', 'base64:7OM+mwk3EDNRX51/1RJm5qc6oeDs/77I9FXlG5VS4zE=');

    }



}
