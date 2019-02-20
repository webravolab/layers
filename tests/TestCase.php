<?php

abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected $consoleOutput;

    protected function getPackageProviders($app)
    {
        return [
            // your package service provider,
            Orchestra\Database\ConsoleServiceProvider::class,
        ];
    }

    protected function getPackageAlias($app)
    {
        return [];
    }
    
    protected function getEnvironmentSetUp($app)
    {
        // Test configuration
        $app['config']->set('app.TEST_KEY_001','value001');
        $app['config']->set('app.timezone','UTC');

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

    }

    public function tearDown()
    {
        parent::tearDown();

        $this->consoleOutput = '';
    }

    public function createTempFiles($files = [])
    {
        // Nothing to do
    }

    public function resolveApplicationConsoleKernel($app)
    {
        $app->singleton('artisan', function ($app) {
            return new \Illuminate\Console\Application($app, $app['events'], $app->version());
        });

        $app->singleton('Illuminate\Contracts\Console\Kernel', Kernel::class);

        $app->singleton('Webravo\Infrastructure\Service\GuidServiceInterface', 'Webravo\Infrastructure\Service\GuidService');
    }

    public function artisan($command, $parameters = [])
    {
        parent::artisan($command, array_merge($parameters, ['--no-interaction' => true]));
    }

    public function consoleOutput()
    {
        return $this->consoleOutput ?: $this->consoleOutput = $this->app[Kernel::class]->output();
    }
}
