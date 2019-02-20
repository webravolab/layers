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
        // return ['Webravo\Infrastructure\Service\ConfigurationServiceInterface','Webravo\Infrastructure\Library\Configuration'];
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

        // exec('rm -rf '.__DIR__.'/temp/*');
    }

    public function tearDown()
    {
        parent::tearDown();

        // exec('rm -rf '.__DIR__.'/temp/*');

        $this->consoleOutput = '';
    }

    public function createTempFiles($files = [])
    {
        /*
        foreach ($files as $dir => $dirFiles) {
            mkdir(__DIR__.'/temp/'.$dir);

            foreach ($dirFiles as $file => $content) {
                if (is_array($content)) {
                    mkdir(__DIR__.'/temp/'.$dir.'/'.$file);

                    foreach ($content as $subDir => $subContent) {
                        mkdir(__DIR__.'/temp/vendor/'.$file.'/'.$subDir);
                        foreach ($subContent as $subFile => $subsubContent) {
                            file_put_contents(__DIR__.'/temp/'.$dir.'/'.$file.'/'.$subDir.'/'.$subFile.'.php', $subsubContent);
                        }
                    }
                } else {
                    file_put_contents(__DIR__.'/temp/'.$dir.'/'.$file.'.php', $content);
                }
            }
        }
        */
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
