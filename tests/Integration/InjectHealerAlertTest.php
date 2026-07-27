<?php

namespace Ginganomercy\Guciravel\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Ginganomercy\Guciravel\GuciravelServiceProvider;
use Ginganomercy\Guciravel\HealerEngine;
use Ginganomercy\Guciravel\Middleware\InjectHealerAlert;
use Illuminate\Database\Events\QueryExecuted;

class InjectHealerAlertTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [GuciravelServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Force debug and local environment for testing injection
        $app['config']->set('app.debug', true);
        $app['config']->set('app.env', 'local');
    }

    public function test_it_does_not_inject_alert_into_json_responses(): void
    {
        $engine = $this->app->make(HealerEngine::class);
        $middleware = new InjectHealerAlert($engine);

        $request = Request::create('/api/users', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = new Response('{"status":"ok"}', 200, ['Content-Type' => 'application/json']);

        $result = $middleware->handle($request, fn () => $response);

        $this->assertEquals('{"status":"ok"}', $result->getContent());
    }

    public function test_it_does_not_inject_alert_when_no_n_plus_one_detected(): void
    {
        $engine = $this->app->make(HealerEngine::class);
        $middleware = new InjectHealerAlert($engine);

        $request = Request::create('/', 'GET');
        $html = '<html><body><h1>Hello World</h1></body></html>';
        $response = new Response($html, 200, ['Content-Type' => 'text/html']);

        $result = $middleware->handle($request, fn () => $response);

        $this->assertEquals($html, $result->getContent());
    }

    public function test_it_does_not_inject_alert_when_response_is_gzip_compressed(): void
    {
        $engine = $this->app->make(HealerEngine::class);
        $this->simulateNPlusOne($engine);

        $middleware = new InjectHealerAlert($engine);
        $request = Request::create('/', 'GET');
        $html = '<html><body><h1>Hello World</h1></body></html>';
        $response = new Response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Encoding' => 'gzip',
        ]);

        $result = $middleware->handle($request, fn () => $response);

        $this->assertEquals($html, $result->getContent());
    }

    public function test_it_injects_alert_before_body_close_tag_when_n_plus_one_detected(): void
    {
        $engine = $this->app->make(HealerEngine::class);
        $this->simulateNPlusOne($engine);

        $middleware = new InjectHealerAlert($engine);
        $request = Request::create('/', 'GET');
        $html = '<html><body><h1>Hello World</h1></body></html>';
        $response = new Response($html, 200, ['Content-Type' => 'text/html']);

        $result = $middleware->handle($request, fn () => $response);

        $this->assertStringContainsString('guciravel', strtolower($result->getContent()));
        $this->assertStringContainsString('N+1 Detected!', $result->getContent());
    }

    protected function simulateNPlusOne(HealerEngine $engine): void
    {
        $connection = new class {
            public function getName() { return 'sqlite'; }
        };
        for ($i = 0; $i < 4; $i++) {
            $event = new QueryExecuted('select * from posts where user_id = ?', [], null, $connection);
            $reflection = new \ReflectionMethod(HealerEngine::class, 'analyzeQuery');
            $reflection->setAccessible(true);
            $reflection->invoke($engine, $event);
        }
    }
}
