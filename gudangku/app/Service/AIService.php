<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AIService
{
    protected string $model = 'llama3';

    public function callLlama(string $prompt): string
    {
        $response = Http::timeout(120)->post('http://localhost:11434/api/generate', [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false
        ]);

        return trim($response['response'] ?? '');
    }

    public function getExamples(): array
    {
        return Cache::remember('sql_examples', now()->addDay(), function () {
            return json_decode(file_get_contents(storage_path('AI/sql_examples.json')), true);
        });
    }

    public function findRelevantExamples(string $question, int $limit = 5): array
    {
        $examples = $this->getExamples();
        $keywords = explode(' ', strtolower($question));
        $scored = array_map(function ($ex) use ($keywords) {
            $q = strtolower($ex['question']);

            $score = 0;
            foreach ($keywords as $k) {
                if (str_contains($q, $k)) $score++;
            }

            return [...$ex, 'score' => $score];
        }, $examples);

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice(array_filter($scored, fn($e) => $e['score'] > 0), 0, $limit);
    }

    public function selectSQLFromExamples(string $userPrompt): string
    {
        $examples = $this->findRelevantExamples($userPrompt);

        // fallback if nothing matched
        if (empty($examples)) $examples = $this->getExamples();

        $exampleBlock = '';
        foreach ($examples as $ex) {
            $exampleBlock .= "\nQ: {$ex['question']}\nSQL: {$ex['sql']}\n";
        }

        $prompt = "
You are an AI that selects the most relevant SQL query.

STRICT RULES:
- ONLY return ONE SQL query
- DO NOT explain
- DO NOT add anything else
- DO NOT modify query structure
- Keep placeholders like :param
- DO NOT modify query structure

SELECTION RULE:
- Choose the most similar question
- Prefer exact keyword match
- Prefer most specific query

=== EXAMPLES ===
$exampleBlock

=== USER QUESTION ===
$userPrompt

=== OUTPUT ===
";

        $sql = $this->callLlama($prompt);

        return trim($sql);
    }

    public function extractParams(string $question): array {
        $params = [];

        // Inventory Room
        if (preg_match('/(ruangan|room|lokasi|tempat|di)\s(.+)/i', $question, $m)) $params['room'] = '%'.trim($m[1]).'%';

        // Inventory Category
        if (preg_match('/(kategori|category|jenis|tipe)\s(.+)/i', $question, $m)) $params['category'] = '%'.trim($m[1]).'%';

        // Inventory Name
        if (preg_match('/(nama|name|barang|item|produk)\s(.+)/i', $question, $m)) $params['name'] = '%'.trim($m[1]).'%';

        // Inventory Price
        if (preg_match('/(harga|price)\s(.+)/i', $question, $m)) $params['name'] = '%'.trim($m[1]).'%';

        // Inventory Merk
        if (preg_match('/(merk|brand|buatan)\s(.+)/i', $question, $m)) $params['name'] = '%'.trim($m[1]).'%';

        return $params;
    }

    public function buildBindings(array $params, string $userId): array {
        $bindings = array_values($params);
        $bindings[] = $userId;

        return $bindings;
    }

    public function generateNarration(string $question, array $data): string {
        $json = json_encode($data);

        // Currency detection
        $isMoney = preg_match('/harga|pengeluaran|biaya|cost|price|total/i', $question);
        $moneyRule = $isMoney ? "- Format angka dengan Rp (contoh: Rp 3.000.000)" : "- Tulis angka dalam format digit (contoh: 3.000)";

        // Datetime detection
        $hasDatetime = preg_match('/\d{4}-\d{2}-\d{2}/', $json);
        $datetimeRule = $hasDatetime ? "- Format tanggal: DD Month YYYY at hh:mm AM/PM" : "";

        $prompt = "
Kamu adalah asisten yang menjelaskan hasil query.

ATURAN:
- Gunakan Bahasa Indonesia
- Jangan sebut 'data' atau 'JSON'
- Jawaban HARUS singkat dan langsung ke inti
- JANGAN membuat narasi panjang
- Fokus hanya pada hasil
- Gunakan format sederhana dan jelas
- Jangan ulangi pertanyaan
- $moneyRule
- $datetimeRule

Pertanyaan:
$question

Hasil:
$json

Jawaban:
";

        return $this->callLlama($prompt);
    }
}