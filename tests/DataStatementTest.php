<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class DataStatementTest extends DetectionOnDocumentTest
{
    private $patternsDataStatement = [
        ["data", "statement"],
        ["declaration", "of", "research", "data", "availability"],
        ["data", "availability", "statement"],
        ["dados", "de", "pesquisa"],
        ["declaração", "de", "disponibilidade", "de", "dados", "da", "pesquisa"],
        ["datos", "de", "investigación"]
    ];

    public function testDetectsDataStatement(): void
    {
        $documentWords = $this->documentChecker->words;

        foreach ($this->patternsDataStatement as $pattern) {
            $this->documentChecker->words = $this->insertWordsIntoDocWordList($pattern, $documentWords);
            $this->assertEquals("Success", $this->documentChecker->checkDataStatement());
        }
    }

    public function testDoesntDetectDataStatementWhenNotPresent(): void
    {
        $this->assertEquals("Error", $this->documentChecker->checkDataStatement());
    }
}
