# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-07-27

### Added
- **Visual N+1 Alert Panel:** Floating diagnostic UI injected before `</body>` when N+1 queries are detected in HTML web responses.
- **Event-Driven Architecture:** Uses `Illuminate\Support\Facades\Event::listen(QueryExecuted::class)` to track executed SQL statements without eager PDO database connection overhead.
- **Execution Stack Tracer:** Automatically locates the exact Blade/PHP file and line number responsible for the N+1 query trigger.
- **Enterprise Safety Guards:**
  - Automatic deactivation when `APP_DEBUG=false` or `APP_ENV=production`.
  - RAM usage protection with a maximum threshold of 1,000 tracked queries per request/worker loop.
  - Standalone CLI/Unit Test safety via `function_exists('base_path')` fallback.
- **Automated Verification Suite:** PHPUnit 10 and Orchestra Testbench 9 integration tests covering UI injection, threshold limits, SQL deduplication, Gzip output buffer safety, and JSON API compatibility.
- **Semantic Versioning:** Programmatic version inspection via `HealerEngine::VERSION` and `HealerEngine::version()`.
