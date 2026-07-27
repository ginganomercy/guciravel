<div align="center">
    <h1>♨️ Guciravel</h1>
    <p><strong>The Visual N+1 Query Healer for Laravel</strong></p>
    <p><i>Detect, Trace, and Heal Laravel Performance Issues Visually.</i></p>
    <p>
        <img src="https://img.shields.io/badge/version-1.0.0-ef4444" alt="Version 1.0.0">
        <img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php" alt="PHP 8.0+">
        <img src="https://img.shields.io/badge/Laravel-9.x--12.x-FF2D20?logo=laravel" alt="Laravel 9.x - 12.x">
        <img src="https://img.shields.io/badge/Composer-2.x-885630?logo=composer" alt="Composer 2.x">
    </p>
</div>

---

## 📖 What is Guciravel?
**Guciravel** is an *N+1 Query* detection library tailored specifically for *Development* environments. Inspired by the Guci Hot Springs in Tegal, Indonesia (famous for its healing properties), this library "heals" the chronic *N+1 Query* disease that silently kills your Laravel application's performance.

Unlike Laravel's built-in `preventLazyLoading()` feature which throws a disruptive error page, Guciravel acts like a **Secret Intelligence Agent**. It allows your application to run normally, but if it detects an *N+1 Query*, it seamlessly injects a highly informative **Alert Panel** into the corner of your browser.

## 🛡️ Enterprise-Grade Safety
Guciravel is engineered with absolute **Production Safety** in mind:
- **Zero Overhead in Production:** The library completely disables itself if `APP_DEBUG=false` or if your app is running in `production`.
- **Memory Leak Proof (RAM Safe):** It features a built-in memory limiter (max 1000 tracked queries). It will never exhaust your server's RAM, even when running in Long-Running processes like Queue Workers (`php artisan queue:work`).
- **CLI Protected:** It automatically deactivates during Console/Terminal execution. It strictly intercepts Web Requests (HTML) only.

---

## 🖥️ Server Requirements & Compatibility (Versioning)

Guciravel is engineered for wide enterprise compatibility across both legacy and modern Laravel LTS/stable releases. Its dependencies are decoupled from heavy application layers and bind strictly to `illuminate/support`, `illuminate/database`, and `illuminate/contracts`.

### 1. Supported PHP & Laravel Versions
| Component | Supported Version Ranges | Minimum Requirement | Notes |
|---|---|---|---|
| **PHP Runtime** | `8.0`, `8.1`, `8.2`, `8.3`, `8.4` | `^8.0` | Strict types enabled; PHP 7.x intentionally unsupported |
| **Laravel Framework** | `9.x`, `10.x`, `11.x`, `12.x` | `^9.0` | Full support for Laravel 11/12 streamlined bootstrap |
| **Composer** | `2.x` | `^2.0` | Utilizes PSR-4 autoloading & Laravel package auto-discovery |

### 2. Composer Dependency Matrix
| Composer Package Requirement | Version Constraint | Purpose |
|---|---|---|
| `php` | `^8.0` | Core PHP engine and typed property compatibility |
| `illuminate/support` | `^9.0\|^10.0\|^11.0\|^12.0` | Event Dispatcher & Collection utilities |
| `illuminate/database` | `^9.0\|^10.0\|^11.0\|^12.0` | `QueryExecuted` event interception & Eloquent model tracing |
| `illuminate/contracts` | `^9.0\|^10.0\|^11.0\|^12.0` | Laravel IoC container and Http Kernel contracts |

---

## 📦 Installation

Since this is purely a development tool, it is **highly recommended** to install it as a `--dev` dependency with semantic versioning:

```bash
composer require ginganomercy/guciravel:"^1.0" --dev
```

*This package utilizes Laravel Auto-Discovery. You do not need to register the Service Provider manually.*

---

## 🚀 Usage

There is **Zero Configuration required**. 
Simply serve your Laravel application locally as you normally would.

### Detection Scenario:
1. You define an Eloquent relationship (e.g., `User` has many `Posts`).
2. You iterate through them without *Eager Loading* in your Blade view:
   ```blade
   @foreach(App\Models\User::all() as $user)
       <!-- THIS IS AN N+1 TRIGGER! -->
       Total Posts: {{ $user->posts->count() }} 
   @endforeach
   ```
3. When you load the page in your browser, the **Guciravel Panel** will automatically pop up in the bottom right corner of your screen.

### What Does Guciravel Tell You?
The Guciravel panel provides a detailed forensic diagnosis:
1. 🧮 **Duplication Count:** How many times the exact same query was executed.
2. 💾 **Raw SQL:** The raw SQL statement causing the bottleneck.
3. 📍 **File Location:** **(CRITICAL!)** Guciravel traces the execution stack and pinpoints the specific file name and line number (e.g., `resources/views/users.blade.php (Line: 12)`).
4. 💊 **Healing Remedy:** Recommendation to fix the issue using the `with()` method.

---

## ⚠️ Recommendations & Best Practices

### 1. When to use `with()`?
Guciravel will advise you to use eager loading. This means you should change your Eloquent calls from:
```php
// ❌ Bad (Triggers N+1)
$users = User::all();
```
To:
```php
// ✅ Good (Eager Loading)
$users = User::with('posts')->get();
```

### 2. Does this replace `preventLazyLoading()`?
No, both can work side-by-side. However, if you enable `Model::preventLazyLoading(! app()->isProduction());` in your `AppServiceProvider`, your application will crash (throw an Exception) before Guciravel has a chance to render its beautiful UI.

If you prefer a visual, educational approach (especially useful when working with Junior developers who need exact file locations), you can rely entirely on Guciravel without enabling `preventLazyLoading()`.

### 3. SPA & API Compatibility
Guciravel is designed **exclusively** to inject pure HTML pages.
- If your application is serving API responses (JSON), Guciravel will silently step aside to prevent breaking your JSON structure.
- If you are using the **TurboBlade** library, Guciravel is fully compatible and its alert panel will persist seamlessly across page navigations.

---

## 🏛️ Enterprise Architecture & Zero-PDO Overhead

Guciravel uses an **Event-Driven Interceptor** architecture (`Illuminate\Support\Facades\Event::listen(QueryExecuted::class)`). 
- **No Unnecessary Database Connections:** Unlike traditional libraries that bind directly to `DB::listen` (which can force an eager PDO database connection initialization during package booting), Guciravel listens to the Laravel Event Dispatcher.
- **Safe CLI & Standalone Fallback:** The execution stack tracer uses `function_exists('base_path')` with fallback to `getcwd()`, ensuring 100% safety when running standalone unit tests or external CLI scripts.

---

## 🧪 Testing & Verification

Guciravel comes with a complete automated test suite powered by **PHPUnit 10** and **Orchestra Testbench 9**.

To run the verification suite locally:
```bash
# 1. Install development dependencies
composer install

# 2. Run the test suite
composer test
# or
vendor/bin/phpunit
```

The test suite covers:
- **Unit Tests:** Threshold limits (queries ≤ 3 ignored), duplicate SQL hash detection, and 1,000-query RAM memory protection.
- **Integration Tests:** Middleware HTML injection before `</body>`, JSON API non-interference, and Gzip binary stream protection.

---

## 👨‍💻 Author
**Rafly A.R**
📧 Email: raflypriyantoro@gmail.com
📸 Instagram: [@galaxy_scream](https://instagram.com/galaxy_scream)

---

## 📜 License
This library is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).