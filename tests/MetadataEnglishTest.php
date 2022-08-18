<?php

use PHPUnit\Framework\TestCase;

require_once("DetectionOnDocumentTest.php");

class MetadataEnglishTest extends DetectionOnDocumentTest
{
    private $patternKeyword = array("keywords");
    private $patternAbstract = array("abstract");
    private $title = "A beautiful title";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetectionKeyword(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternKeyword, $this->documentChecker->words);
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($this->title);

        $this->assertEquals("Success", $statusMetadata['keywords']);
    }

    public function testDetectionAbstract(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternAbstract, $this->documentChecker->words);
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($this->title);

        $this->assertEquals("Success", $statusMetadata['abstract']);
    }

    public function testDetectionTitle(): void
    {
        $parser = new ContentParser();
        $patternTitle = $parser->createPatternFromString($this->title);

        $this->documentChecker->words = $this->insertWordsIntoDocWordList($patternTitle, $this->documentChecker->words);
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($this->title);

        $this->assertEquals("Success", $statusMetadata['title']);
    }

    public function testDoesntDetectKeyword(): void
    {
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($this->title);
        $this->assertEquals("Error", $statusMetadata['keywords']);
    }

    public function testDoesntDetectAbstract(): void
    {
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($this->title);
        $this->assertEquals("Error", $statusMetadata['abstract']);
    }

    public function testDoesntDetectTitle(): void
    {
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($this->title);
        $this->assertEquals("Error", $statusMetadata['title']);
    }

    public function testDetectionEmptyTitle(): void
    {
        $emptyTitle = "";
        $statusMetadata = $this->documentChecker->checkMetadataInEnglish($emptyTitle);

        $this->assertEquals("Error", $statusMetadata['title']);
    }
}
