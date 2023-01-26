<?php

use PHPUnit\Framework\TestCase;

require_once("DetectionOnDocumentTest.php");

class MetadataEnglishTest extends DetectionOnDocumentTest
{
    private $patternKeywords = array("keywords");
    private $patternAbstract = array("abstract");
    private $title = "A beautiful title";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetectionKeywords(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternKeywords, $this->documentChecker->words);
        $statusKeywords = $this->documentChecker->checkKeywordsInEnglish($this->title);

        $this->assertEquals("Success", $statusKeywords);
    }

    public function testDetectionAbstract(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternAbstract, $this->documentChecker->words);
        $statusAbstract = $this->documentChecker->checkAbstractInEnglish($this->title);

        $this->assertEquals("Success", $statusAbstract);
    }

    public function testDetectionTitle(): void
    {
        $parser = new ContentParser();
        $patternTitle = $parser->createPatternFromString($this->title);

        $this->documentChecker->words = $this->insertWordsIntoDocWordList($patternTitle, $this->documentChecker->words);
        $statusTitle = $this->documentChecker->checkTitleInEnglish($this->title);

        $this->assertEquals("Success", $statusTitle);
    }

    public function testDoesntDetectKeywords(): void
    {
        $statusKeywords = $this->documentChecker->checkKeywordsInEnglish($this->title);
        $this->assertEquals("Error", $statusKeywords);
    }

    public function testDoesntDetectAbstract(): void
    {
        $statusAbstract = $this->documentChecker->checkAbstractInEnglish($this->title);
        $this->assertEquals("Error", $statusAbstract);
    }

    public function testDoesntDetectTitle(): void
    {
        $statusTitle = $this->documentChecker->checkTitleInEnglish($this->title);
        $this->assertEquals("Error", $statusTitle);
    }

    public function testDetectionEmptyTitle(): void
    {
        $emptyTitle = "";
        $statusTitle = $this->documentChecker->checkTitleInEnglish($emptyTitle);

        $this->assertEquals("Error", $statusTitle);
    }
}
