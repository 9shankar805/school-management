<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$paper = App\Models\QuestionPaper::with(['sections.questions'])->find(4);
echo "Paper: " . $paper->title . "\n";
echo "Orientation: " . $paper->orientation . "\n";

$allFlatItems = [];
foreach ($paper->sections as $section) {
    foreach ($section->questions as $qIndex => $q) {
        $allFlatItems[] = [
            'section'      => $section,
            'is_sec_first' => ($qIndex === 0),
            'question'     => $q,
        ];
    }
}
echo "Total questions: " . count($allFlatItems) . "\n";

function estimateH($item, $isNewSec) {
    $h = $isNewSec ? 38 : 0;
    $q = $item['question'];
    $text = strip_tags($q->question_text);
    $lines = max(1, (int) ceil(mb_strlen($text) / 75));
    $h += ($lines * 18) + 8;
    if (!empty($q->options)) {
        $totOpt = 0;
        foreach ($q->options as $o) {
            $totOpt += mb_strlen(is_array($o) ? ($o['text'] ?? '') : (string)$o) + 8;
        }
        $h += ($totOpt <= 80 ? 22 : 44);
    }
    return $h;
}

$runningH = 0;
$secId = null;
foreach ($allFlatItems as $idx => $item) {
    $isNewSec = ($secId !== $item['section']->id);
    $h = estimateH($item, $isNewSec);
    $runningH += $h;
    $secId = $item['section']->id;
    echo "Q".($idx+1)." [".$item['section']->title."] itemH: $h | cumH: $runningH\n";
}
