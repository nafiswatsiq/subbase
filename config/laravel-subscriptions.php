<?php

declare(strict_types=1);

use Nafiswatsiq\Subbase\Models\Feature;
use Nafiswatsiq\Subbase\Models\Plan;
use Nafiswatsiq\Subbase\Models\Subscription;
use Nafiswatsiq\Subbase\Models\SubscriptionUsage;

return [

    /*
    |--------------------------------------------------------------------------
    | Locale Currency Mapping
    |--------------------------------------------------------------------------
    |
    | Configure locale to currency mapping for multi-language plan prices.
    | Key can be locale code (en, id, ms, en-US) and value must be
    | ISO 4217 currency code.
    |
    */

    'default_currency' => 'USD',

    'locale_currency_map' => [
        // --- Asia Tenggara ---
        'id' => 'IDR', // Indonesia / Indonesian
        'ms' => 'MYR', // Malay
        'my' => 'MYR', // Malaysia
        'sg' => 'SGD', // Singapore
        'th' => 'THB', // Thailand / Thai
        'ph' => 'PHP', // Philippines
        'tl' => 'PHP', // Tagalog / Filipino
        'vn' => 'VND', // Vietnam
        'vi' => 'VND', // Vietnamese

        // --- Bahasa / Negara Utama Global ---
        'en' => 'USD', // English (Default: US Dollar)
        'us' => 'USD', // United States
        'gb' => 'GBP', // United Kingdom
        'uk' => 'GBP', // UK (Alternatif)
        'ca' => 'CAD', // Canada
        'au' => 'AUD', // Australia
        'nz' => 'NZD', // New Zealand

        // --- Asia Timur & Selatan ---
        'ja' => 'JPY', // Japanese
        'jp' => 'JPY', // Japan
        'ko' => 'KRW', // Korean
        'kr' => 'KRW', // South Korea
        'zh' => 'CNY', // Chinese (Mainland)
        'cn' => 'CNY', // China
        'tw' => 'TWD', // Taiwan
        'hk' => 'HKD', // Hong Kong
        'in' => 'INR', // India
        'hi' => 'INR', // Hindi
        'bn' => 'BDT', // Bengali / Bangladesh

        // --- Eropa ---
        'de' => 'EUR', // German / Germany
        'fr' => 'EUR', // French / France
        'it' => 'EUR', // Italian / Italy
        'es' => 'EUR', // Spanish / Spain
        'nl' => 'EUR', // Dutch / Netherlands
        'fi' => 'EUR', // Finnish / Finland
        'el' => 'EUR', // Greek / Greece
        'ch' => 'CHF', // Switzerland
        'ru' => 'RUB', // Russian / Russia
        'pl' => 'PLN', // Polish / Poland
        'sv' => 'SEK', // Swedish / Sweden
        'da' => 'DKK', // Danish / Denmark
        'no' => 'NOK', // Norwegian / Norway
        'cs' => 'CZK', // Czech / Czech Republic
        'hu' => 'HUF', // Hungarian / Hungary
        'tr' => 'TRY', // Turkish / Turkey

        // --- Timur Tengah & Afrika ---
        'ar' => 'SAR', // Arabic (Default contoh: Saudi Riyal)
        'sa' => 'SAR', // Saudi Arabia
        'ae' => 'AED', // United Arab Emirates
        'za' => 'ZAR', // South Africa

        // --- Amerika Latin ---
        'pt' => 'BRL', // Portuguese (Sering diarahkan ke Brazilian Real)
        'br' => 'BRL', // Brazil
        'mx' => 'MXN', // Mexico
        'ar_ar' => 'ARS', // Argentina (contoh jika pakai ar_ar untuk membedakan dengan bahasa Arab 'ar')
    ],

    'language_locale_map' => [
        'af' => 'Afrikaans',
        'ar' => 'Arabic',
        'az' => 'Azerbaijani',
        'be' => 'Belarusian',
        'bg' => 'Bulgarian',
        'bn' => 'Bengali',
        'bs' => 'Bosnian',
        'ca' => 'Catalan',
        'cs' => 'Czech',
        'da' => 'Danish',
        'de' => 'German',
        'el' => 'Greek',
        'en' => 'English',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fa' => 'Persian',
        'fi' => 'Finnish',
        'fr' => 'French',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hr' => 'Croatian',
        'hu' => 'Hungarian',
        'hy' => 'Armenian',
        'id' => 'Indonesian',
        'is' => 'Icelandic',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'ka' => 'Georgian',
        'kk' => 'Kazakh',
        'ko' => 'Korean',
        'lt' => 'Lithuanian',
        'lv' => 'Latvian',
        'mk' => 'Macedonian',
        'ms' => 'Malay',
        'nb' => 'Norwegian Bokmål',
        'nl' => 'Dutch',
        'nn' => 'Norwegian Nynorsk',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'sq' => 'Albanian',
        'sr' => 'Serbian',
        'sv' => 'Swedish',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'uk' => 'Ukrainian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        'vi' => 'Vietnamese',
        'zh' => 'Chinese',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Tables
    |--------------------------------------------------------------------------
    |
    |
    */

    'tables' => [
        'plans' => 'plans',
        'features' => 'features',
        'subscriptions' => 'subscriptions',
        'subscription_usage' => 'subscription_usage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Models
    |--------------------------------------------------------------------------
    |
    | Models used to manage subscriptions. You can replace to use your own models,
    | but make sure that you have the same functionalities or that your models
    | extend from each model that you are going to replace.
    |
    */

    'models' => [
        'plan' => Plan::class,
        'feature' => Feature::class,
        'subscription' => Subscription::class,
        'subscription_usage' => SubscriptionUsage::class,
    ],

];
