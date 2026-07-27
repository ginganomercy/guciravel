<?php

namespace Ginganomercy\Guciravel;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\QueryExecuted;

class HealerEngine
{
    /**
     * Package version.
     */
    public const VERSION = '1.0.0';

    /**
     * Get the package version.
     */
    public static function version(): string
    {
        return self::VERSION;
    }

    /**
     * Threshold for considering a query as N+1
     *
     * @var int
     */
    protected int $threshold = 3;

    /**
     * History of queries in the current request.
     *
     * @var array
     */
    protected array $queries = [];

    /**
     * List of detected N+1 queries.
     *
     * @var array
     */
    protected array $nPlusOneQueries = [];

    /**
     * Start listening to database queries via Laravel Event Dispatcher.
     * This avoids initializing PDO database connections unnecessarily.
     */
    public function listen(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            $this->analyzeQuery($query);
        });
    }

    /**
     * Analyze a single query.
     */
    protected function analyzeQuery(QueryExecuted $query): void
    {
        // Memory protection: prevent RAM exhaustion in long-running processes
        if (count($this->queries) >= 1000) {
            $this->queries = [];
            $this->nPlusOneQueries = [];
        }

        // Ignore transaction commands and framework metadata queries
        if (in_array(strtolower($query->sql), ['begin', 'commit', 'rollback']) || str_contains($query->sql, 'sqlite_master')) {
            return;
        }

        // We use the raw SQL (without bindings) to group N+1 queries
        // Because N+1 queries have the same structure but different binding values
        $hash = md5($query->sql);

        if (!isset($this->queries[$hash])) {
            $this->queries[$hash] = [
                'sql' => $query->sql,
                'count' => 1,
                'source' => null,
            ];
        } else {
            $this->queries[$hash]['count']++;
        }

        // If threshold exceeded and not yet marked as N+1
        if ($this->queries[$hash]['count'] > $this->threshold && !isset($this->nPlusOneQueries[$hash])) {
            // Find the source file and line where this query originated
            $source = $this->findSource();
            $this->queries[$hash]['source'] = $source;
            $this->nPlusOneQueries[$hash] = &$this->queries[$hash];
        }
    }

    /**
     * Returns the detected N+1 queries.
     */
    public function getDetectedQueries(): array
    {
        return array_values($this->nPlusOneQueries);
    }

    /**
     * Determine if any N+1 query was detected.
     */
    public function hasIssues(): bool
    {
        return count($this->nPlusOneQueries) > 0;
    }

    /**
     * Trace back to find the application file that triggered the query.
     */
    protected function findSource(): string
    {
        // Limit backtrace for performance
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);

        foreach ($trace as $frame) {
            if (!isset($frame['file'])) {
                continue;
            }

            $file = $frame['file'];

            // Skip vendor files (Laravel core) and Guciravel itself
            if (!str_contains($file, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) && 
                !str_contains($file, 'HealerEngine.php')) {
                
                // Make path relative to base path for cleaner display
                $basePath = function_exists('base_path') ? base_path() : getcwd();
                $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file);
                
                // Normalize slashes for display
                $relativePath = str_replace('\\', '/', $relativePath);
                
                return $relativePath . ' (Line: ' . $frame['line'] . ')';
            }
        }

        return 'Unknown source';
    }
}
