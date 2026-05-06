<?php
require_once __DIR__ . '/../services/OpenAI.php';
require_once __DIR__ . '/../models/CodeHistory.php';

class CodeController
{
    public function index()
    {
        $model = new CodeHistory();
        return $history = $model->all();
    }

    /* Function to fetch response from LLM api */
    public function explain($code)
    {
        $service = new OpenAI();
        $result = $service->processCode($code);

        $model = new CodeHistory();
        $model->save($code, $result);

        header("Location: index.php?flg=1");
    }


    /* Helper Function to check code is in Python or JavaScript */
    public static function checkLanguage2($code)
    {
        $code = strtolower($code);
        if (strpos($code, 'def ') !== false ||
        strpos($code, 'import ') !== false ||
        strpos($code, 'print(') !== false ||
        strpos($code, 'range(') !== false) return 'Python';
        
        if (strpos($code, 'function') !== false ||
        strpos($code, 'console.log') !== false ||
        strpos($code, '=>') !== false ||
        strpos($code, 'const ') !== false ||
        strpos($code, 'let ') !== false) return 'JavaScript';
        return 'Unknown';
    }

    public static function checkLanguage($code) {
        $code = strtolower($code);

        // Python patterns
        if (preg_match('/\bdef\b|\bimport\b|print\(|range\(|:\s*\n/', $code)) {
            return 'Python';
        }

        // JavaScript patterns
        if (preg_match('/\bfunction\b|=>|console\.log|var |let |const /', $code)) {
            return 'JavaScript';
        }

        return 'Unknown';
    }

    /* Helper Function to hightlight code using basic keywords */
    public static function highlightCode($code) {
        $keywords = ['function','def','for','while','if','else','return','const','var','let','console.log','try','except','finally','print','async'];
        foreach ($keywords as $word) {
            $code = preg_replace("/\\b($word)\\b/",
                "<span style='color:blue;font-weight:bold;'>$1</span>", $code);
        }
        return $code;
    }
}