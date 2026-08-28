<?php

namespace App\Infrastructure\Logging\Listeners;

use Illuminate\Events\Dispatcher;
use App\Domain\Municipios\Events\MunicipioCreatedEvent;
use App\Domain\Municipios\Events\MunicipioUpdatedEvent;
use App\Domain\Municipios\Events\MunicipioDeletedEvent;
use Illuminate\Support\Facades\{Log, Auth};

/**
 * Listener que registra auditoria de operações em Municipios.
 */
class AuditMunicipioListener
{
    /**
     * Register the listeners for the subscriber.
     */
    public static function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(
            MunicipioCreatedEvent::class,
            [AuditMunicipioListener::class, 'onCreated']
        );

        $dispatcher->listen(
            MunicipioUpdatedEvent::class,
            [AuditMunicipioListener::class, 'onUpdated']
        );

        $dispatcher->listen(
            MunicipioDeletedEvent::class,
            [AuditMunicipioListener::class, 'onDeleted']
        );
    }

    /**
     * Log quando um município é criado.
     */
    public function onCreated(MunicipioCreatedEvent $event): void
    {
        Log::info('municipio.created', [
            'municipio_id' => $event->municipio->id,
            'municipio_nome' => $event->municipio->nome,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log quando um município é atualizado.
     */
    public function onUpdated(MunicipioUpdatedEvent $event): void
    {
        Log::info('municipio.updated', [
            'municipio_id' => $event->municipio->id,
            'municipio_nome' => $event->municipio->nome,
            'old_values' => $event->oldValues,
            'new_values' => $event->municipio->getChanges(),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log quando um município é deletado.
     */
    public function onDeleted(MunicipioDeletedEvent $event): void
    {
        Log::warning('municipio.deleted', [
            'municipio_id' => $event->municipio->id,
            'municipio_nome' => $event->municipio->nome,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'deleted_at' => now(),
        ]);
    }
}
