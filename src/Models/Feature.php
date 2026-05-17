<?php

namespace Nafiswatsiq\Subbase\Models;

use Nafiswatsiq\Subbase\Models\Concerns\ReplacesTranslatableJsonOnMassAssignment;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravelcm\Subscriptions\Models\Feature as BaseFeature;
use Spatie\Sluggable\SlugOptions;

class Feature extends BaseFeature
{
    use ReplacesTranslatableJsonOnMassAssignment;

    protected static function boot(): void
    {
        // Instead, manually call the boot chain we need
        static::bootTraits();

        // Now register our corrected event listeners
        static::deleted(function (Feature $feature): void {
            // Use the correct relationship name: usages() instead of usage()
            $feature->usages()->delete();
        });

        static::creating(function (Feature $feature) {
            if (static::query()
                ->where('plan_id', '=', $feature->plan_id)
                ->where('slug', '=', $feature->slug)
                ->exists()) {
                throw new InvalidArgumentException('Each plan should only have one feature with the same slug');
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom(function (Model $model): string {
                if (! $model instanceof self) {
                    return '';
                }

                $names = $model->getTranslations('name');

                return $names !== [] ? (string) reset($names) : '';
            })
            ->allowDuplicateSlugs()
            ->saveSlugsTo('slug');
    }

    public static function localeLanguageMap(): array
    {
        $configMap = config('subbase.language_locale_map', []);
        $normalized = [];

        foreach ((array) $configMap as $locale => $language) {
            $localeKey = strtolower(trim((string) $locale));
            $languageName = trim((string) $language);

            if ($localeKey === '' || $languageName === '') {
                continue;
            }

            $normalized[$localeKey] = $languageName;
        }

        return $normalized;
    }

    /**
     * Locale key from app locale that exists in {@see localeLanguageMap()}, for form defaults.
     */
    public static function defaultFormLanguage(): string
    {
        $map = static::localeLanguageMap();
        $locale = strtolower(str_replace('_', '-', app()->getLocale()));

        if (array_key_exists($locale, $map)) {
            return $locale;
        }

        $prefix = explode('-', $locale)[0];

        if (array_key_exists($prefix, $map)) {
            return $prefix;
        }

        $first = array_key_first($map);

        return $first !== null ? $first : 'en';
    }

    /**
     * @param  array<string, string>  $nameByLocale
     * @param  array<string, string>  $descriptionByLocale
     * @return array<int, array{language: string, name: string, description: string}>
     */
    public static function repeaterRowsFromTranslations(array $nameByLocale, array $descriptionByLocale): array
    {
        $orderedLocales = [];

        foreach (array_keys($nameByLocale) as $locale) {
            $orderedLocales[(string) $locale] = true;
        }

        foreach (array_keys($descriptionByLocale) as $locale) {
            $key = (string) $locale;
            if (! array_key_exists($key, $orderedLocales)) {
                $orderedLocales[$key] = true;
            }
        }

        $rows = [];
        foreach (array_keys($orderedLocales) as $localeKey) {
            $rows[] = [
                'language' => $localeKey,
                'name' => (string) ($nameByLocale[$localeKey] ?? ''),
                'description' => (string) ($descriptionByLocale[$localeKey] ?? ''),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>|mixed>  $rows
     * @return array{name: array<string, string>, description: array<string, string>}
     */
    public static function translationsFromRepeaterRows(array $rows): array
    {
        $name = [];
        $description = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $locale = strtolower(trim((string) ($row['language'] ?? '')));
            if ($locale === '') {
                continue;
            }

            $name[$locale] = (string) ($row['name'] ?? '');
            $description[$locale] = (string) ($row['description'] ?? '');
        }

        return [
            'name' => $name,
            'description' => $description,
        ];
    }
}
