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

        // Inject test models
        putenv('JOBS_ELOQUENT_MODEL=App\JobsLayerTest');
        putenv('JOBS_QUEUE_ELOQUENT_MODEL=App\JobsQueueLayerTest');
        putenv('EVENTS_ELOQUENT_MODEL=App\EventsLayerTest');
        putenv('COMMAND_ELOQUENT_MODEL=App\CommandsLayerTest');

        $credentials_file = __DIR__ . '/google-credentials.json';
        if (file_exists($credentials_file)) {
            // Read from credentials file
            $credentials = json_decode(file_get_contents($credentials_file));

            // Google Storage configuration
            $app['config']->set('google.service', [
                'enable' => true,
                'file' => 'google-credentials.json'
            ]);
            $app['config']->set('google.project_id', $credentials->project_id);
            $app['config']->set('google.scopes', [\Google_Service_Storage::DEVSTORAGE_FULL_CONTROL]);
            $app['config']->set('google.access_type', 'online');
            $app['config']->set('google.approval_prompt', 'auto');
            $app['config']->set('google.prompt', 'select_account');
            $app['config']->set('google.bucket', 'test-bucket');
            $app['config']->set('google.image_cache_ttl', 86400);
            $app['config']->set('google.gzip', true);
            $app['config']->set('google.application_name', 'test-application');

            $app['config']->set('rabbitmq.host', '35.246.112.111');
            $app['config']->set('rabbitmq.port', '5672');
            $app['config']->set('rabbitmq.user', 'webravo-develop');
            $app['config']->set('rabbitmq.password', 'pTe5MYH!4@8E');
            $app['config']->set('rabbitmq.virtual_host', 'develop');
        }
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

        $app->singleton('Webravo\Infrastructure\Service\DataStoreServiceInterface', 'Webravo\Persistence\Service\DataStoreService');
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
