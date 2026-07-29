<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normalizes the `no_hp` input field before it reaches controllers.
 *
 * Rules:
 *  - Strip all spaces, dashes, and + signs
 *  - Convert leading 0 to 62 (Indonesian country code)
 *  - Leave numbers already starting with 62 untouched
 *
 * Example: "0812-1234-5678" → "6281212345678"
 */
class NormalizePhoneNumber
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('no_hp')) {
            $request->merge([
                'no_hp' => self::normalize($request->input('no_hp')),
            ]);
        }

        return $next($request);
    }

    public static function normalize(?string $nomor): string
    {
        if (! $nomor) {
            return '';
        }

        // Strip spaces, dashes, plus signs
        $nomor = preg_replace('/[\s\-\+]/', '', $nomor);

        // Convert leading 0 to 62
        if (str_starts_with($nomor, '0')) {
            $nomor = '62'.substr($nomor, 1);
        }

        return $nomor;
    }

    /**
     * Format a stored/normalized phone number back to the local 08xxx format
     * for display in UI, PDFs, and customer-facing views.
     *
     * Example: "628981234567" → "0898-1234-567"
     */
    public static function display(?string $nomor): string
    {
        if (! $nomor) {
            return '';
        }

        $normalized = self::normalize($nomor);

        // Convert 62xxx to 0xxx
        if (str_starts_with($normalized, '62')) {
            $local = '0'.substr($normalized, 2);

            // Insert dashes: 0812-3456-7890
            return preg_replace('/^(\d{4})(\d{4})(\d+)$/', '$1-$2-$3', $local);
        }

        return $nomor;
    }
}
