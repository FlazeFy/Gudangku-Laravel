<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class JUnitReporter
{
    public static function send(string $xmlPath): array
    {
        $xml = self::loadXml($xmlPath);

        $payload = [];
        self::extractTestCases($xml->testsuite, $payload);

        $response = Http::timeout(30)->acceptJson()->post('http://127.0.0.1:9001/api/v1/automation-dev-test-log', $payload);
        if (!$response->successful()) throw new \Exception('Unable to send report : '.$response->status().' '.$response->body());

        return $response->json();
    }

    private static function loadXml(string $xmlPath): \SimpleXMLElement
    {
        if (!file_exists($xmlPath)) throw new \Exception("JUnit XML not found : {$xmlPath}");

        libxml_use_internal_errors(true);

        $xml = simplexml_load_file($xmlPath);
        if (!$xml) throw new \Exception('Invalid junit.xml');

        return $xml;
    }

    private static function extractTestCases(\SimpleXMLElement $suite, array &$payload): void {
        foreach ($suite->testcase as $testCase) {
            $payload[] = self::buildPayload($testCase);
        }

        foreach ($suite->testsuite as $childSuite) {
            self::extractTestCases($childSuite, $payload);
        }
    }

    private static function buildPayload(\SimpleXMLElement $testCase): array {
        return [
            'app_name' => config('app.name'),
            'platform' => 'Web',
            'testing_type' => 'Integration',
            'test_suite' => (string) $testCase['class'],
            'test_name' => (string) $testCase['name'],
            'assertions' => (int) $testCase['assertions'],
            'time' => (float) $testCase['time'],
            'is_passed' => self::isPassed($testCase),
            'record_at' => now()->toIso8601String(),
        ];
    }

    private static function isPassed(\SimpleXMLElement $testCase): bool {
        return !$testCase->failure->count() && !$testCase->error->count();
    }
}