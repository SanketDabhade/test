<?php

class OpenAI
{
    private $apiKey;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->apiKey = $config['openrouter_api'];
    }

    public function processCode($code, $includeComplexity = false)
    {
        $extra = $includeComplexity ? "Also include time and space complexity." : "";

        $prompt = "You are a senior software engineer.

        1. Explain the following code in 2-4 simple sentences.
        2. Detect technical language
        3. Then provide an optimized version of the code.
        4. $extra

        Return ONLY valid JSON. No extra text.

        Format:
        {
        \"explanation\": \"...\",
        \"language\": \"...\",
        \"optimized_code\": \"...\",
        \"complexity\": \"...\"
        }

        Rules:
        - Do not hallucinate
        - If unclear, say so

        Code:
        $code";

        $prompt = "You are a senior software engineer.

        Step 1: Determine if the given code is written in Python or JavaScript.

        Step 2:
        - If it is Python or JavaScript:
            - Explain the code in 2–4 sentences
            - Provide an optimized version
            - Provide time and space complexity
        - If it is NOT Python or JavaScript OR the code is unclear/incomplete:
            - Return:
            {
                \"explanation\": \"Code is unclear or not supported.\",
                \"language\": \"\",
                \"optimized_code\": \"N/A\",
                \"complexity\": \"N/A\"
            }

        Return ONLY valid JSON. Do not include any extra text.

        Format:
        {
        \"explanation\": \"...\",
        \"language\": \"...\",
        \"optimized_code\": \"...\",
        \"complexity\": \"...\"
        }

        Code:
        $code";

        $data = [
            "model" => "baidu/cobuddy:free",
            "messages" => [
                [
                "role" => "user",
                "content" => $prompt
                ]
            ],
            "temperature" => 0.3
        ];
        
        $ch = curl_init("https://openrouter.ai/api/v1/chat/completions");
        //$ch = curl_init("https://api.openai.com/v1/chat/completions");

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->apiKey,
                "Content-Type: application/json",
                "HTPP-Referer: http://localhost", //openrouter change
                "X-Title: Code Explainer"        //openrouter change
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        //var_dump($response);
        //exit;
        if (curl_errno($ch)) {
            return ["explanation" => curl_error($ch)];
        }

        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? '';

        // Extract JSON block safely
        preg_match('/\{[\s\S]*\}/', $content, $matches);

        $jsonString = $matches[0] ?? '';

        $parsed = json_decode($jsonString, true);

        if (!$parsed) {
            // fallback manual parsing
            return fallbackParser($content);
        }

        return $parsed;
    }

    public function fallbackParser($text) {
        return [
            "explanation" => extractSection($text, "explanation"),
            "language" => extractSection($text, "language"),
            "optimized_code" => extractSection($text, "optimized"),
            "complexity" => extractSection($text, "complexity")
        ];
    }

    public function extractSection($text, $keyword) {
        $keywordSafe = preg_quote($keyword, '/');
        if (preg_match("/{$keywordSafe}[:\-]\s*(.*)/i", $text, $matches)) {
            return trim($matches[1]);
        }
        return "Not clearly provided";
    }
}