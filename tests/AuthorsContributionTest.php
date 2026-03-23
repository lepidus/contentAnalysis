<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTestCase;

class AuthorsContributionTest extends DetectionOnDocumentTestCase
{
    private $patternContribution = array("contribuição", "dos", "autores");

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetection(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternContribution, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkAuthorsContribution());
    }

    public function testDoesntDetectWhenNotPresent(): void
    {
        $this->assertEquals("Error", $this->documentChecker->checkAuthorsContribution());
    }
}
