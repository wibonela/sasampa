<?php

namespace App\Providers;

use App\Services\Messaging\AfricasTalkingProvider;
use App\Services\Messaging\MessagingProviderInterface;
use App\Services\Messaging\MetaWhatsAppProvider;
use App\Services\Messaging\PindoProvider;
use App\Services\Messaging\StubProvider;
use App\Services\WhatsappReceiptService;
use Illuminate\Support\ServiceProvider;

class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessagingProviderInterface::class, function () {
            return match (config('messaging.provider', 'stub')) {
                'meta' => new MetaWhatsAppProvider(),
                'africas_talking' => new AfricasTalkingProvider(),
                'pindo' => new PindoProvider(),
                default => new StubProvider(),
            };
        });

        $this->app->singleton(WhatsappReceiptService::class);
    }

    public function boot(): void
    {
        //
    }
}
