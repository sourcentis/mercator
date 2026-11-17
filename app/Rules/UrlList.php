<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Comma-separated list of web URLs (http(s)://...) and Windows network
 * paths (\\server\share\path). Network paths may contain spaces and
 * accented characters, but no comma since it is the list separator.
 */
class UrlList implements ValidationRule
{
    /**
     * Server name: no whitespace, no path separator, no character forbidden by Windows
     */
    private const SERVER = '[^\\\\\/\s<>:"|?*,]+';

    /**
     * Share or path segment: spaces allowed, no control or Windows-forbidden character
     */
    private const SEGMENT = '[^\\\\\/\x00-\x1F<>:"|?*,]+';

    /**
     * @param  string  $attribute  The attribute being validated
     * @param  mixed  $value  The current value of the attribute
     * @param  Closure  $fail  Closure to be run in case of failure
     */
     public function validate(string $attribute, mixed $value, Closure $fail): void
     {
         if (! is_string($value)) {
             $fail($this->message());
     
             return;
         }
     
         foreach (self::entries($value) as $entry) {
             if (
                 ! self::isWebUrl($entry)
                 && ! self::isMarkdownLink($entry)
                 && ! self::isNetworkPath($entry)
             ) {
                 $fail($this->message());
     
                 return;
             }
         }
     }

    /**
     * Normalize every entry of the list (see normalizeEntry()) and drop empty ones.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return implode(',', self::entries($value));
    }

    /**
     * Split the list into normalized, non-empty entries.
     *
     * @return string[]
     */
    public static function entries(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (string $entry) => self::normalizeEntry(trim($entry)), explode(',', $value)),
            fn (string $entry) => $entry !== ''
        ));
    }

    /**
     * Convert network path notations to the Windows UNC form (\\server\share\path):
     * //server/share/path, file://server/share/path and \\server/share/path.
     * Web URLs and anything else are returned untouched.
     */
    public static function normalizeEntry(string $entry): string
    {
        if (preg_match('/^file:\/\//i', $entry)) {
            $path = rawurldecode(substr($entry, strlen('file://')));
        } elseif (str_starts_with($entry, '\\\\') || str_starts_with($entry, '//')) {
            $path = substr($entry, 2);
        } else {
            return $entry;
        }

        return '\\\\'.str_replace('/', '\\', ltrim($path, '/\\'));
    }

    public static function isWebUrl(string $entry): bool
    {
        // spaces are tolerated, filter_var() only accepts them encoded
        return preg_match('/^https?:\/\//i', $entry) === 1
            && filter_var(str_replace(' ', '%20', $entry), FILTER_VALIDATE_URL) !== false;
    }

    public static function isNetworkPath(string $entry): bool
    {
        return preg_match(
            '/^\\\\\\\\'.self::SERVER.'(\\\\'.self::SEGMENT.')+\\\\?$/u',
            $entry
        ) === 1;
    }
    
    public static function isMarkdownLink(string $entry): bool
    {
        return self::parseMarkdownLink($entry) !== null;
    }

    public static function parseMarkdownLink(string $entry): ?array
    {
        if (! preg_match('/^\[([^\]]+)\]\((https?:\/\/.+)\)$/u', $entry, $matches)) {
            return null;
        }
    
        if (! self::isWebUrl($matches[2])) {
            return null;
        }
    
        return [
            'label' => $matches[1],
            'url' => $matches[2],
        ];
    }

    public function message(): string
    {
        return 'Invalid URL List';
    }
}
