<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class ConflictInterestTest extends DetectionOnDocumentTest
{
    private $patternConflict = ["conflicts", "of", "interests"];
    private $patternConflictLigature = ["conﬂito", "de", "interesses"];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetection(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternConflict, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkConflictInterest());
    }

    public function testDetectionLigature(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternConflictLigature, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkConflictInterest());
    }

    public function testDoesntDetectWhenNotPresent(): void
    {
        $this->assertEquals("Error", $this->documentChecker->checkConflictInterest());
    }
}
