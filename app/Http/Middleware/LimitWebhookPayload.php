<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class LimitWebhookPayload
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('hook/*')) {
            $limit = (int) config('inspector.max_payload');
            abort_if((int) $request->header('Content-Length', 0) > $limit, 413);
            abort_if(strlen(json_encode($request->headers->all(), JSON_INVALID_UTF8_SUBSTITUTE)) > 16384, 431);
            $stream = $request->getContent(true);
            $raw = stream_get_contents($stream, $limit + 1);
            abort_if(strlen($raw) > $limit, 413);
            if (stream_get_meta_data($stream)['seekable']) rewind($stream);
            $request->attributes->set('inspector_raw', $raw);
        }
        return $next($request);
    }
}
