<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Nafiswatsiq\Subbase\Support\ThemeManager;

class SubbaseThemeInstallCommand extends Command
{
    protected $signature = 'subbase:theme-install
                            {theme? : Name of the theme to install or activate}
                            {--publish : Publish theme views to resources/views/vendor}
                            {--force : Overwrite existing published theme views}';

    protected $description = 'Set active theme or publish theme view files for customization';

    public function handle(ThemeManager $themeManager): int
    {
        $theme = $this->argument('theme');

        if (! $theme) {
            $available = $themeManager->availableThemes();
            $theme = $this->choice('Select a theme to activate:', $available, 0);
        }

        $theme = strtolower(trim((string) $theme));

        $this->updateEnvFile($theme);
        $this->info("Active theme set to: <comment>{$theme}</comment>");

        if ($this->option('publish')) {
            $this->publishThemeViews($theme, (bool) $this->option('force'));
        } else {
            $this->line('To customize this theme, run: <comment>php artisan subbase:theme-install ' . $theme . ' --publish</comment>');
        }

        return self::SUCCESS;
    }

    protected function updateEnvFile(string $theme): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->warn('.env file not found. Set SUBBASE_THEME=' . $theme . ' manually in your environment.');
            return;
        }

        $content = File::get($envPath);
        if (preg_match('/^SUBBASE_THEME=/m', $content)) {
            $content = preg_replace('/^SUBBASE_THEME=.*$/m', 'SUBBASE_THEME=' . $theme, $content);
        } else {
            $content = rtrim($content) . PHP_EOL . 'SUBBASE_THEME=' . $theme . PHP_EOL;
        }

        File::put($envPath, $content);
        $this->info('.env file updated with SUBBASE_THEME=' . $theme);
    }

    protected function publishThemeViews(string $theme, bool $force): void
    {
        $packages = [
            'subbase' => dirname(__DIR__, 3) . '/resources/views/themes/' . $theme,
            'subbase-payment' => dirname(__DIR__, 4) . '/subbase-payment/resources/views/themes/' . $theme,
        ];

        foreach ($packages as $pkg => $srcDir) {
            if (! File::isDirectory($srcDir)) {
                $this->warn("No source views found for theme [{$theme}] in [{$pkg}].");
                continue;
            }

            $destDir = resource_path("views/vendor/{$pkg}/themes/{$theme}");

            if (File::isDirectory($destDir) && ! $force) {
                $this->warn("Directory [{$destDir}] already exists. Use --force to overwrite.");
                continue;
            }

            File::copyDirectory($srcDir, $destDir);
            $this->info("Published [{$pkg}] views for theme [{$theme}] to [{$destDir}].");
        }
    }
}
