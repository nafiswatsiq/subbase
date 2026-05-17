<?php

namespace Nafiswatsiq\Subbase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Nafiswatsiq\Subbase\Models\Feature;
use Nafiswatsiq\Subbase\Models\Plan;
use Nafiswatsiq\Subbase\Models\Subscription;
use Nafiswatsiq\Subbase\Models\SubscriptionUsage;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubbaseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('subbase')
            ->hasConfigFile('subbase')
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->startWith(function (Command $artisanCommand): void {
                        $artisanCommand->info('Installing laravelcm/laravel-subscriptions...');

                        $artisanCommand->call('subscriptions:install', [
                            '--no-interaction' => true,
                        ]);

                        $artisanCommand->info('Installing subbase custom migrations...');
                    })
                    ->publishConfigFile()
                    ->endWith(function (Command $artisanCommand): void {
                        $sourceMigration = dirname(__DIR__).'/database/migrations/add_prices_to_plans_table.php';
                        $timestamp = now();
                        $destinationMigration = database_path('migrations/'.$timestamp->format('Y_m_d_His').'_add_prices_to_plans_table.php');

                        while (File::exists($destinationMigration)) {
                            $timestamp = $timestamp->addSecond();
                            $destinationMigration = database_path('migrations/'.$timestamp->format('Y_m_d_His').'_add_prices_to_plans_table.php');
                        }

                        File::copy($sourceMigration, $destinationMigration);

                        $artisanCommand->info('Published custom migration: '.basename($destinationMigration));

                        $artisanCommand->info('Subbase installation completed.');
                    })
                    ->askToStarRepoOnGitHub('nafiswatsiq/subbase');
            });
    }

    public function packageBooted(): void
    {
        config([
            'laravel-subscriptions.default_currency' => config('subbase.default_currency', 'USD'),
            'laravel-subscriptions.locale_currency_map' => config('subbase.locale_currency_map', []),
            'laravel-subscriptions.language_locale_map' => config('subbase.language_locale_map', []),

            'laravel-subscriptions.tables.plans' => config('subbase.tables.plans', 'plans'),
            'laravel-subscriptions.tables.features' => config('subbase.tables.features', 'features'),
            'laravel-subscriptions.tables.subscriptions' => config('subbase.tables.subscriptions', 'subscriptions'),
            'laravel-subscriptions.tables.subscription_usage' => config('subbase.tables.subscription_usage', 'subscription_usage'),

            'laravel-subscriptions.models.plan' => config('subbase.models.plan', Plan::class),
            'laravel-subscriptions.models.feature' => config('subbase.models.feature', Feature::class),
            'laravel-subscriptions.models.subscription' => config('subbase.models.subscription', Subscription::class),
            'laravel-subscriptions.models.subscription_usage' => config('subbase.models.subscription_usage', SubscriptionUsage::class),
        ]);
    }
}
