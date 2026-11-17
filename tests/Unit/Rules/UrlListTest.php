<?php

use App\Rules\UrlList;
use Illuminate\Support\Facades\Validator;

function urlListPasses(string $value): bool
{
    return Validator::make(['v' => UrlList::normalize($value)], ['v' => new UrlList])->passes();
}

describe('normalize', function () {
    test('converts network path notations to UNC form', function (string $input, string $expected) {
        expect(UrlList::normalize($input))->toBe($expected);
    })->with([
        'UNC with spaces and accents' => ['\\\\srv\\Mon Partage\\Procédures\\Guide v2.pdf', '\\\\srv\\Mon Partage\\Procédures\\Guide v2.pdf'],
        'double slash' => ['//srv/Mon Partage/doc.pdf', '\\\\srv\\Mon Partage\\doc.pdf'],
        'file URL, percent-encoded' => ['file://srv/Mon%20Partage/doc.pdf', '\\\\srv\\Mon Partage\\doc.pdf'],
        'mixed separators' => ['\\\\srv/share/doc.pdf', '\\\\srv\\share\\doc.pdf'],
    ]);

    test('leaves web URLs untouched', function () {
        expect(UrlList::normalize('https://example.com/a b'))->toBe('https://example.com/a b');
    });

    test('trims entries and drops empty ones', function () {
        expect(UrlList::normalize(' https://a.com , //srv/x y ,, '))->toBe('https://a.com,\\\\srv\\x y');
    });

    test('keeps null', function () {
        expect(UrlList::normalize(null))->toBeNull();
    });
});

describe('validate', function () {
    test('accepts web URLs and network paths', function (string $value) {
        expect(urlListPasses($value))->toBeTrue();
    })->with([
        'https' => ['https://example.com/doc'],
        'http with space' => ['http://intranet/my doc'],
        'UNC with spaces and accents' => ['\\\\srv\\Mon Partage\\Données\\doc #1.pdf'],
        'hidden share' => ['\\\\srv\\partage$\\'],
        'legacy double slash' => ['//srv/share/path'],
        'legacy file URL' => ['file://srv/share/path'],
        'list' => ['https://a.com, \\\\srv\\share\\x'],
        'empty' => [''],
    ]);

    test('rejects invalid entries', function (string $value) {
        expect(urlListPasses($value))->toBeFalse();
    })->with([
        'no scheme' => ['example.com'],
        'ftp' => ['ftp://example.com'],
        'server only' => ['\\\\srv'],
        'space in server name' => ['\\\\my srv\\share'],
        'forbidden character' => ['\\\\srv\\share\\a:b'],
        'empty segment' => ['\\\\srv\\\\share'],
        'local file URL' => ['file:///C:/doc.pdf'],
        'one bad entry in list' => ['https://a.com, toto'],
    ]);
});
