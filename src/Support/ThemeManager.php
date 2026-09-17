<?php

namespace Nafiswatsiq\Subbase\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ThemeManager
{
    public const BUILTIN_THEMES = [
        'default',
        'neo-brutalism',
        'glassmorphism',
        'claymorphism',
        'cyberpunk',
        'maximalism',
    ];

    public function getTheme(): string
    {
        return config('subbase.theme', 'default');
    }

    public function resolveView(string $package, string $view): string
    {
        $theme = $this->getTheme();

        $themedView = "{$package}::themes.{$theme}.{$view}";
        if (View::exists($themedView)) {
            return $themedView;
        }

        $defaultView = "{$package}::themes.default.{$view}";
        if (View::exists($defaultView)) {
            return $defaultView;
        }

        return "{$package}::{$view}";
    }

    public function availableThemes(): array
    {
        $themes = self::BUILTIN_THEMES;

        $customPath = resource_path('views/vendor/subbase/themes');
        if (File::exists($customPath)) {
            $dirs = File::directories($customPath);
            foreach ($dirs as $dir) {
                $name = basename($dir);
                if (! in_array($name, $themes, true)) {
                    $themes[] = $name;
                }
            }
        }

        return $themes;
    }
}
