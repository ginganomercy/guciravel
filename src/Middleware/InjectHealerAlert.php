<?php

namespace Ginganomercy\Guciravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Ginganomercy\Guciravel\HealerEngine;

class InjectHealerAlert
{
    protected HealerEngine $engine;

    public function __construct(HealerEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only inject into HTML responses and if there are issues
        if ($this->shouldInject($request, $response) && $this->engine->hasIssues()) {
            $this->injectAlert($response);
        }

        return $response;
    }

    /**
     * Determine if we should inject the alert into the response.
     */
    protected function shouldInject(Request $request, Response $response): bool
    {
        // Don't inject on AJAX, API, or non-HTML responses
        if ($request->ajax() || $request->isJson() || $request->wantsJson()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');
        if (!str_contains(strtolower($contentType ?? ''), 'text/html')) {
            return false;
        }

        // Don't inject on redirects or server errors
        if ($response->isRedirection() || $response->isServerError()) {
            return false;
        }

        return true;
    }

    /**
     * Inject the rendered Blade alert into the HTML body.
     */
    protected function injectAlert(Response $response): void
    {
        $content = $response->getContent();
        
        $queries = $this->engine->getDetectedQueries();
        
        $alertHtml = view('guciravel::alert', ['queries' => $queries])->render();

        $pos = strripos($content, '</body>');
        
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . $alertHtml . substr($content, $pos);
            $response->setContent($content);
        }
    }
}
