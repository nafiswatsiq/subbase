<?php

namespace Nafiswatsiq\Subbase;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Nafiswatsiq\Subbase\Filament\Resources\Plans\PlanResource;
use Nafiswatsiq\Subbase\Filament\Resources\Subscriptions\SubscriptionResource;

class SubbasePlugin implements Plugin
{
    public function getId(): string
    {
        return 'subbase';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PlanResource::class,
            SubscriptionResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
      // 
    }

    public static function make(): static
    {
        return app(static::class);
    }
}