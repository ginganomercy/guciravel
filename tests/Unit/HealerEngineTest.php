<?php

namespace Ginganomercy\Guciravel\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ginganomercy\Guciravel\HealerEngine;
use Illuminate\Database\Events\QueryExecuted;

class HealerEngineTest extends TestCase
{
    protected HealerEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new HealerEngine();
    }

    public function test_it_does_not_flag_query_below_or_equal_threshold(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->simulateQuery('SELECT * FROM users WHERE id = ?');
        }

        $this->assertFalse($this->engine->hasIssues());
        $this->assertCount(0, $this->engine->getDetectedQueries());
    }

    public function test_it_flags_query_as_n_plus_one_when_exceeding_threshold(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->simulateQuery('SELECT * FROM users WHERE id = ?');
        }

        $this->assertTrue($this->engine->hasIssues());
        $this->assertCount(1, $this->engine->getDetectedQueries());
        $this->assertEquals(4, $this->engine->getDetectedQueries()[0]['count']);
    }

    public function test_it_ignores_transaction_and_sqlite_metadata_queries(): void
    {
        $this->simulateQuery('begin');
        $this->simulateQuery('commit');
        $this->simulateQuery('select * from sqlite_master');

        $this->assertFalse($this->engine->hasIssues());
        $this->assertCount(0, $this->engine->getDetectedQueries());
    }

    public function test_it_resets_history_when_memory_limit_reached(): void
    {
        // Simulate 1000 different queries to reach memory threshold
        for ($i = 0; $i < 1000; $i++) {
            $this->simulateQuery("SELECT * FROM table_{$i}");
        }

        // On the 1001st query, the buffer should reset
        $this->simulateQuery("SELECT * FROM table_reset");

        $reflection = new \ReflectionProperty(HealerEngine::class, 'queries');
        $reflection->setAccessible(true);
        $queries = $reflection->getValue($this->engine);

        $this->assertCount(1, $queries);
    }

    protected function simulateQuery(string $sql): void
    {
        $connection = new class {
            public function getName() { return 'sqlite'; }
        };
        $event = new QueryExecuted($sql, [], null, $connection);
        $reflection = new \ReflectionMethod(HealerEngine::class, 'analyzeQuery');
        $reflection->setAccessible(true);
        $reflection->invoke($this->engine, $event);
    }
}
