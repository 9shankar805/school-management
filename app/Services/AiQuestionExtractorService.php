<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class AiQuestionExtractorService
{
    protected FreeAiService $freeAiService;

    public function __construct(FreeAiService $freeAiService)
    {
        $this->freeAiService = $freeAiService;
    }

    /**
     * Extract questions structured by sections from an uploaded file or text prompt.
     *
     * @param UploadedFile|null $file
     * @param string|null $topicOrPrompt
     * @param string $difficulty
     * @param string $targetGrade
     * @return array
     * @throws Exception
     */
    public function generateSectionsAndQuestions(?UploadedFile $file, ?string $topicOrPrompt, string $difficulty = 'mixed', string $targetGrade = 'Secondary', ?string $targetSectionTitle = null): array
    {
        $isImage = false;
        $imageBase64 = null;
        $mimeType = 'image/jpeg';

        if ($file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                $isImage = true;
                $mimeType = $file->getMimeType() ?: ('image/' . ($ext === 'jpg' ? 'jpeg' : $ext));
                $imageBase64 = base64_encode(file_get_contents($file->getRealPath()));
            }
        }

        if ($isImage && $imageBase64) {
            $prompt = $this->buildPrompt(
                "EXAM PAPER IMAGE ANALYSIS INSTRUCTION:\n" .
                "Read and transcribe this uploaded question paper image carefully. Extract ALL questions, section titles, instructions, multiple choice options, and marks visible in the paper image.\n" .
                ($topicOrPrompt ? "TEACHER NOTES / TOPIC: " . $topicOrPrompt : ""),
                $difficulty,
                $targetGrade,
                $targetSectionTitle
            );

            $rawAiResponse = $this->freeAiService->generateVision($prompt, $imageBase64, $mimeType);
            return $this->parseJsonResponse($rawAiResponse);
        }

        $extractedText = '';

        if ($file) {
            $extractedText = $this->extractTextFromFile($file);
        }

        $combinedContent = trim(($extractedText ? "EXTRACTED DOCUMENT CONTENT:\n" . $extractedText . "\n\n" : "") . ($topicOrPrompt ? "TEACHER INSTRUCTIONS / TOPIC:\n" . $topicOrPrompt : ""));

        if (empty($combinedContent)) {
            throw new Exception("Please provide either an uploaded reference document or a topic/instruction prompt.");
        }

        // Limit content length if extremely large to fit AI context window
        if (mb_strlen($combinedContent) > 8000) {
            $combinedContent = mb_substr($combinedContent, 0, 8000) . "\n...[truncated for AI processing]";
        }

        $prompt = $this->buildPrompt($combinedContent, $difficulty, $targetGrade, $targetSectionTitle);
        $rawAiResponse = $this->freeAiService->generate($prompt);

        return $this->parseJsonResponse($rawAiResponse);
    }

    /**
     * Extract raw text content from uploaded file based on MIME type / extension.
     */
    protected function extractTextFromFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        switch ($extension) {
            case 'txt':
                return file_get_contents($path) ?: '';

            case 'docx':
                return $this->extractTextFromDocx($path);

            case 'pdf':
                return $this->extractTextFromPdf($path);

            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'webp':
                return "[Image file uploaded: " . $file->getClientOriginalName() . ". Generate a set of comprehensive exam questions based on standard curriculum topics appropriate for this subject.]";

            default:
                // Fallback attempt to read plain text
                $content = @file_get_contents($path);
                return $content ? strip_tags($content) : '';
        }
    }

    /**
     * Extract text from .docx file using ZipArchive.
     */
    protected function extractTextFromDocx(string $filePath): string
    {
        $text = '';
        $zip = new ZipArchive();

        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                if ($data) {
                    $xml = simplexml_load_string($data);
                    if ($xml) {
                        $text = strip_tags($xml->asXML());
                    }
                }
            } else {
                $zip->close();
            }
        }

        return preg_replace('/\s+/', ' ', $text);
    }

    /**
     * Extract text stream from PDF file.
     */
    protected function extractTextFromPdf(string $filePath): string
    {
        $content = @file_get_contents($filePath);
        if (!$content) {
            return '';
        }

        // Basic stream extraction for plain text PDFs
        preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches);
        $text = '';
        foreach ($matches[1] as $stream) {
            $uncompressed = @gzuncompress($stream);
            if ($uncompressed) {
                $text .= ' ' . preg_replace('/[^\x20-\x7E\s]/', '', $uncompressed);
            } else {
                $text .= ' ' . preg_replace('/[^\x20-\x7E\s]/', '', $stream);
            }
        }

        $cleanText = preg_replace('/\s+/', ' ', $text);
        return trim($cleanText) ?: "[PDF Document content read]";
    }

    /**
     * Build strict JSON output prompt for Groq / Pollinations.
     */
    protected function buildPrompt(string $content, string $difficulty, string $targetGrade, ?string $targetSectionTitle = null): string
    {
        $sectionInstruction = $targetSectionTitle
            ? "1. TARGET SECTION: \"{$targetSectionTitle}\". Extract, format, or generate ALL questions specifically for this single section (\"{$targetSectionTitle}\"). Do not create other sections."
            : "1. Organize the questions into logical sections (e.g. Group A: Multiple Choice Questions, Group B: Short Answer Questions, Group C: Long Answer / Numerical Questions).";

        $specTitle = $targetSectionTitle ?: "Group A: Questions";

        return <<<PROMPT
You are an expert school exam paper setter and AI question organizer.

TASK INSTRUCTIONS:
{$sectionInstruction}
2. If raw or unformatted questions are pasted below (e.g. copied from notes, chat, Word, or rough drafts), clean up their wording, fix grammar/formatting, and extract all questions accurately.
3. If options exist for MCQs (a, b, c, d), format them cleanly into a JSON string array.
4. Infer or assign appropriate allocated marks, difficulty levels (easy, medium, hard), and Bloom's taxonomy levels if not explicitly specified.
5. MATHEMATICAL FORMULAS & EQUATIONS: Always wrap mathematical formulas, fractions, roots, powers, matrices, and algebraic expressions in standard LaTeX inline math delimiters \( ... \) or $ ... $ (for example: \( \sqrt[4]{\left(\frac{132}{143}\right)^{-2}} \) or \( 6 - x - x^2 \)). Do NOT use plain computer text like sqrt[4]((132/143)^-2).

INPUT CONTENT / RAW QUESTIONS / TOPIC:
{$content}

DIFFICULTY LEVEL PREFERENCE: {$difficulty}
TARGET GRADE LEVEL: {$targetGrade}

CRITICAL REQUIREMENT:
Respond ONLY with a valid, raw JSON array of sections. Do not include markdown codeblocks (no ```json), commentary, or extra text before or after the JSON.

JSON FORMAT SPECIFICATION:
[
  {
    "title": "{$specTitle}",
    "instructions": "Answer the questions concisely.",
    "questions": [
      {
        "question_type": "short_answer",
        "question_text": "Sample question text here...",
        "options": null,
        "correct_answer": null,
        "answer_text": "Sample answer or solution here...",
        "allocated_marks": 2,
        "difficulty": "medium",
        "bloom_taxonomy": "understand"
      }
    ]
  }
]

Allowed question_type values: mcq, essay, fill_blank, true_false, short_answer, long_answer, numerical, case_study.
Allowed difficulty values: easy, medium, hard.
Allowed bloom_taxonomy values: remember, understand, apply, analyse, evaluate, create.
PROMPT;
    }

    /**
     * Parse raw AI string into array of sections.
     */
    protected function parseJsonResponse(string $rawAiResponse): array
    {
        // 1. Strip <think>...</think> reasoning blocks from Qwen / DeepSeek models if present
        $clean = preg_replace('/<think>.*?<\/think>/s', '', $rawAiResponse);

        // 2. Strip markdown fences if present
        $clean = preg_replace('/^```(?:json)?\s*/i', '', trim($clean));
        $clean = preg_replace('/\s*```$/i', '', $clean);

        // 3. Find JSON array start and end
        $firstBracket = strpos($clean, '[');
        $lastBracket = strrpos($clean, ']');

        if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
            $clean = substr($clean, $firstBracket, $lastBracket - $firstBracket + 1);
        }

        // Try direct decode
        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $this->cleanLatexDelimiters($decoded);
        }

        // 4. Sanitize JSON string literals:
        //    a) Replace raw unescaped newlines/tabs inside double quotes with \n, \r, \t
        //    b) Escape single backslashes that are invalid JSON escapes (e.g. \(, \), \frac, \sqrt)
        $sanitized = preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"/s', function ($m) {
            $str = $m[0];
            // Escape literal line breaks inside string values
            $str = str_replace(["\r\n", "\n", "\r", "\t"], ["\\n", "\\n", "\\n", "\\t"], $str);
            // Escape invalid single backslashes in JSON like \(, \), \[, \], \frac, \sqrt, \left, \right
            $str = preg_replace('/\\\\([^"\\\\\/bfnrtu])/', '\\\\\\\\$1', $str);
            $str = preg_replace('/\\\\(frac|times|right|root|sqrt|theta|text|tan|cdot|div|pm)/i', '\\\\\\\\$1', $str);
            return $str;
        }, $clean);

        // Remove trailing commas before ] or }
        $sanitized = preg_replace('/,\s*([\]}])/', '$1', $sanitized);

        $decoded = json_decode($sanitized, true);
        if (is_array($decoded)) {
            return $this->cleanLatexDelimiters($decoded);
        }

        // 5. Attempt auto-repair for truncated JSON (close open brackets/braces/strings)
        $repaired = $sanitized;
        if (substr_count($repaired, '"') % 2 !== 0) {
            $repaired .= '"';
        }
        $openBraces = substr_count($repaired, '{') - substr_count($repaired, '}');
        $openBrackets = substr_count($repaired, '[') - substr_count($repaired, ']');
        
        $repaired = preg_replace('/,\s*$/', '', $repaired);
        $repaired = preg_replace('/"[^"]*"\s*:\s*$/', '', $repaired);

        for ($i = 0; $i < $openBraces; $i++) {
            $repaired .= '}';
        }
        for ($i = 0; $i < $openBrackets; $i++) {
            $repaired .= ']';
        }

        $decoded = json_decode($repaired, true);

        if (!is_array($decoded)) {
            Log::error("AI Question Extractor invalid JSON response: " . $rawAiResponse);
            throw new Exception("AI generated response could not be parsed as structured questions. Please try again.");
        }

        return $this->cleanLatexDelimiters($decoded);
    }

    /**
     * Recursively normalize double-escaped LaTeX delimiters (\\( -> \() in question arrays.
     */
    protected function cleanLatexDelimiters(mixed $data): mixed
    {
        if (is_string($data)) {
            return str_replace(['\\\\(', '\\\\)', '\\\\[', '\\\\]'], ['\\(', '\\)', '\\[', '\\]'], $data);
        }
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = $this->cleanLatexDelimiters($v);
            }
        }
        return $data;
    }
}
