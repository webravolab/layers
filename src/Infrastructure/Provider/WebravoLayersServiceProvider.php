<?php
namespace Webravo\Infrastructure\Provider;

use Illuminate\Support\ServiceProvider;

class WebravoLayersServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->bind('Webravo\Infrastructure\Service\FilesystemServiceInterface', 'Webravo\Persistence\Service\FilesystemService');
        $this->app->bind('Webravo\Infrastructure\Service\GuidServiceInterface', 'Webravo\Infrastructure\Service\GuidService');
        $this->app->bind('Webravo\Infrastructure\Service\CdnServiceInterface', 'Webravo\Persistence\Service\CdnService');
        $this->app->bind('Webravo\Infrastructure\Repository\EventRepositoryInterface', 'Webravo\Persistence\Eloquent\Store\EloquentEventStore');
        $this->app->bind('Webravo\Infrastructure\Repository\JobQueueInterface', 'Webravo\Persistence\Eloquent\Store\EloquentJobStore');
        $this->app->bind('Webravo\Infrastructure\Repository\CommandStoreInterface', 'Webravo\Persistence\Eloquent\Store\EloquentCommandStore');
        $this->app->bind('Webravo\Infrastructure\Service\QueueServiceInterface', 'Webravo\Persistence\Service\DBQueueService');
        $this->app->bind('Webravo\Infrastructure\Service\ConfigurationServiceInterface', 'Webravo\Persistence\Service\ConfigurationService');
        $this->app->bind('Webravo\Infrastructure\Service\DataStoreServiceInterface', 'Webravo\Persistence\Service\DataStoreService');
    }

}