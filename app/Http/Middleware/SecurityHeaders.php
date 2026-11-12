<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Random\RandomException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityHeaders
{
    /** Nom du groupe Reporting API (header Reporting-Endpoints / directive report-to). */
    private const REPORT_GROUP = 'csp-endpoint';

    /**
     * @throws RandomException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $mode = config('mercator.csp.mode', 'enforce');
        $cspEnabled = in_array($mode, ['report-only', 'enforce'], true);

        $nonce = null;
        if ($cspEnabled) {
            // Le nonce doit être fixé AVANT $next() : la vue est rendue dans $next().
            // Vite l'ajoute automatiquement aux <script>/<link> générés par @vite ;
            // les scripts inline des vues le récupèrent via @nonce (Vite::cspNonce()).
            $nonce = base64_encode(random_bytes(16));
            Vite::useCspNonce($nonce);
        }

        $response = $next($request);

        // Les réponses binaires et streams ne supportent pas l'ajout de headers
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        if ($cspEnabled) {
            // enforce (défaut) bloque ; report-only se contente de journaliser les violations
            // (storage/logs/security-*.log) — repli à utiliser si une page casse.
            $header = $mode === 'enforce' ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';

            $response->headers->set('Reporting-Endpoints', self::REPORT_GROUP.'="'.url('/csp-report').'"');
            $response->headers->set($header, $this->buildCsp($nonce));
        }

        return $response;
    }

    private function buildCsp(string $nonce): string
    {
        $isDev = app()->environment('local');

        // Serveur Vite (HMR) — uniquement en local
        $viteOrigins = $isDev ? ' http://localhost:5173' : '';
        $viteWs = $isDev ? ' ws://localhost:5173' : '';

        $directives = [
            "default-src 'self'",

            // 'wasm-unsafe-eval' : @hpcc-js/wasm-graphviz (vues réseau d3-graphviz) instancie du WebAssembly
            "script-src 'self' 'nonce-{$nonce}' 'wasm-unsafe-eval'".$viteOrigins,

            // Select2, DataTables, MaxGraph et Chart.js posent des styles inline
            "style-src 'self' 'unsafe-inline'".$viteOrigins,

            // Bootstrap Icons (woff2) servi par Vite en dev ;
            // data: : @sourcentis/bpmn-editor injecte sa police de pictogrammes BPMN en data:font/ttf
            "font-src 'self' data:".$viteOrigins,

            "connect-src 'self'".$viteOrigins.$viteWs,

            "img-src 'self' data:",
            "frame-src 'none'",
            "object-src 'none'",
            "worker-src 'self' blob:",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",

            // report-uri (navigateurs actuels) + report-to (Reporting API)
            'report-uri '.url('/csp-report'),
            'report-to '.self::REPORT_GROUP,
        ];

        return implode('; ', $directives);
    }
}
