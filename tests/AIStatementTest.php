<?php

use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class AIStatementTest extends DetectionOnDocumentTest
{
    private $patternsAIStatement = [
        ["uso", "de", "inteligência", "artificial"],
        ["declaração", "de", "uso", "de", "inteligência", "artificial", "generativa"],
        ["declaração", "de", "uso", "de", "inteligência", "artificial"],
        ["declaração", "de", "uso", "de", "ia"],
        ["declaración", "de", "uso", "de", "inteligencia", "artificial"],
        ["statement", "on", "the", "use", "of", "artificial", "intelligence"],
        ["use", "of", "ai"],
        ["use", "of", "generative", "ai", "tools"],
        ["ai", "use", "statement"]
    ];
    private $falsePositivePatterns = [
        ["inteligência", "artificial"],
        ["artificial", "intelligence"],
        ["ai"]
    ];

    public function testDetectsAiStatement(): void
    {
        $documentWords = $this->documentChecker->words;

        foreach ($this->patternsAIStatement as $pattern) {
            $this->documentChecker->words = $this->insertWordsIntoDocWordList($pattern, $documentWords);
            $this->assertEquals(
                "Success",
                $this->documentChecker->checkAIStatement(),
                implode(' ', $pattern)
            );
        }
    }

    public function testDoesntDetectAiStatementWhenNotPresent(): void
    {
        $this->assertEquals("Warning", $this->documentChecker->checkAIStatement());
    }

    public function testDoesntDetectAiStatementFalsePositives(): void
    {
        $documentWords = $this->documentChecker->words;

        foreach ($this->falsePositivePatterns as $falsePositive) {
            $this->documentChecker->words = $this->insertWordsIntoDocWordList($falsePositive, $documentWords);
            $this->assertEquals(
                "Warning",
                $this->documentChecker->checkAIStatement(),
                implode(' ', $falsePositive)
            );
        }
    }
}
