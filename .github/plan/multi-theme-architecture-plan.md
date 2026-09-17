# Multi-Theme Architecture Plan (Subbase & Subbase-Payment)

## TL;DR
Tambahkan sistem multi-tema untuk semua frontend views (plan-list, checkout, status) + email invoice template.
Tema = variasi Tailwind classes di Blade file berbeda. Tidak ada CSS bundling tambahan.
Diinstall via artisan command, konfigurasi via config/env.

## Scope
- **Included**: plan-list.blade.php (subbase), checkout.blade.php, status.blade.php, mail/invoice.blade.php (subbase-payment)
- **Excluded**: Filament admin panel views (tetap default Filament)

## Tema Built-in
1. `default` — Modern clean (tema saat ini)
2. `neo-brutalism` — Border tebal 3-4px hitam, hard box-shadow, warna bold, font besar
3. `glassmorphism` — Background blur/transparan, border halus, efek kaca
4. `claymorphism` — Rounded besar, inner shadow, warna pastel, efek 3D clay
5. `cyberpunk` — Dark base, neon accent (cyan/magenta), monospace font, glitch aesthetic
6. `maximalism` — Gradient bold, pattern overlay, mixed typography, decorative elements

## Architecture

### 1. View Directory Structure
```
subbase/resources/views/
  themes/
    default/
      components/plan-list.blade.php    <- pindah dari components/plan-list.blade.php
    neo-brutalism/
      components/plan-list.blade.php
    glassmorphism/
      components/plan-list.blade.php
    ...

subbase-payment/resources/views/
  themes/
    default/
      checkout.blade.php                <- pindah dari checkout.blade.php
      status.blade.php                  <- pindah dari status.blade.php
      mail/invoice.blade.php            <- pindah dari mail/invoice.blade.php
    neo-brutalism/
      checkout.blade.php
      status.blade.php
      mail/invoice.blade.php
    ...
```

### 2. Backward Compatibility (Proxy View)
File lama tetap ada tapi jadi proxy ke ThemeManager:
- subbase/resources/views/components/plan-list.blade.php -> include tema aktif
- subbase-payment/resources/views/checkout.blade.php -> include tema aktif
- Ini memastikan user yang sudah publish view tidak break

### 3. ThemeManager Class
subbase/src/Support/ThemeManager.php
- getTheme(): string -- baca dari config('subbase.theme')
- resolveView(string $package, string $view): string -- resolve {package}::themes.{theme}.{view}, fallback ke themes.default.{view}
- availableThemes(): array -- scan themes/ directory
- Singleton, register di SubbaseServiceProvider

### 4. Config Changes
config/subbase.php tambah:
'theme' => env('SUBBASE_THEME', 'default'),
Satu config saja, shared antara subbase & subbase-payment.

### 5. Artisan Commands
- php artisan subbase:theme-list -- tampilkan tema tersedia + mana yang aktif
- php artisan subbase:theme-install {theme} -- set tema di .env + publish view ke host app
- Registrasi di SubbaseServiceProvider::hasCommands()

### 6. View Resolution Changes
- CheckoutController::show() line 45: view() calls resolve via ThemeManager
- CheckoutController status views (line 177, 235, 252): sama
- PaymentInvoiceMail line 34: resolve via ThemeManager
- plan-list.blade.php component: jadi proxy yang @include tema aktif

## Implementation Steps

### Phase 1: Infrastructure (blocking)
1. Tambah 'theme' key di config/subbase.php
2. Buat subbase/src/Support/ThemeManager.php -- singleton, resolveView() + fallback logic
3. Register ThemeManager singleton di SubbaseServiceProvider::packageRegistered()

### Phase 2: Restructure Views (depends on Phase 1)
4. Pindahkan view lama ke themes/default/ (subbase & subbase-payment)
5. Buat proxy view di lokasi lama yang delegate ke ThemeManager (backward compat)
6. Update CheckoutController view() calls (4 tempat)
7. Update PaymentInvoiceMail view reference (1 tempat)

### Phase 3: Artisan Commands (parallel with Phase 2)
8. Buat SubbaseThemeListCommand -- scan themes dir, mark active
9. Buat SubbaseThemeInstallCommand -- update .env SUBBASE_THEME + vendor:publish views
10. Register commands di SubbaseServiceProvider

### Phase 4: Tema Baru (depends on Phase 2)
11. Buat themes/neo-brutalism/ views (plan-list, checkout, status, invoice)
12. Buat themes/glassmorphism/ views
13. Buat themes/claymorphism/ views
14. Buat themes/cyberpunk/ views
15. Buat themes/maximalism/ views

### Phase 5: Testing
16. Test ThemeManager unit test -- resolveView, fallback, availableThemes
17. Test artisan commands -- theme-list output, theme-install writes .env
18. Manual test setiap tema di browser (plan-list, checkout, status page)

## Relevant Files

### Subbase (modify)
- subbase/config/subbase.php -- tambah 'theme' key
- subbase/src/SubbaseServiceProvider.php -- register ThemeManager + commands
- subbase/resources/views/components/plan-list.blade.php -- jadi proxy

### Subbase (create)
- subbase/src/Support/ThemeManager.php
- subbase/src/Console/Commands/SubbaseThemeListCommand.php
- subbase/src/Console/Commands/SubbaseThemeInstallCommand.php
- subbase/resources/views/themes/default/components/plan-list.blade.php
- subbase/resources/views/themes/{tema}/components/plan-list.blade.php x5

### Subbase-Payment (modify)
- subbase-payment/src/Http/Controllers/CheckoutController.php -- 4 view() calls
- subbase-payment/src/Mail/PaymentInvoiceMail.php -- 1 view reference

### Subbase-Payment (create)
- subbase-payment/resources/views/themes/default/checkout.blade.php
- subbase-payment/resources/views/themes/default/status.blade.php
- subbase-payment/resources/views/themes/default/mail/invoice.blade.php
- subbase-payment/resources/views/themes/{tema}/ x5 (checkout, status, invoice per tema)

## Verification
1. php artisan subbase:theme-list -- shows 6 themes, default marked active
2. php artisan subbase:theme-install neo-brutalism -- .env updated, views published
3. Existing view('subbase-payment::checkout') references tetap work (proxy)
4. Tiap tema render tanpa error di browser (plan-list, checkout, status)
5. Email invoice render correct di mail preview
6. PHPUnit tests pass untuk ThemeManager + commands

## Decisions
- Satu config key subbase.theme shared kedua package (tidak perlu duplikasi)
- CSS strategy: pure Tailwind class variation (tidak ada bundling tambahan)
- Backward compat via proxy views, bukan breaking change
- Email invoice juga ikut tema (sesuai request)
- Filament admin views excluded dari theming
