<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Console\Commands;

use Illuminate\Console\Command;
use Nafiswatsiq\Subbase\Support\ThemeManager;

class SubbaseThemeListCommand extends Command
{
    protected $signature = 'subbase:theme-list';

    protected $description = 'List all available Subbase themes and display current active theme';

    public function handle(ThemeManager $themeManager): int
    {
        $activeTheme = $themeManager->getTheme();
        $availableThemes = $themeManager->availableThemes();

        $this->info("Current active theme: <comment>{$activeTheme}</comment>");
        $this->newLine();

        $rows = [];
        foreach ($availableThemes as $theme) {
            $status = ($theme === $activeTheme) ? 'Active' : 'Available';
            $type = in_array($theme, ThemeManager::BUILTIN_THEMES, true) ? 'Built-in' : 'Custom';
            $rows[] = [$theme, $type, $status];
        }

        $this->table(['Theme Name', 'Type', 'Status'], $rows);

        return self::SUCCESS;
    }
}
