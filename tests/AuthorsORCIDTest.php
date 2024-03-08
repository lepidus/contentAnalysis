<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class AuthorsORCIDTest extends DetectionOnDocumentTest
{
    private $validTextOrcids = [
        "https://orcid.org/0000-0001-5727-2427",
        "https://orcid.org/0000-0002-1648-966X",
        "orcid.org/0000-0001-5727-2427"
    ];
    private $invalidTextOrcids = [
        "0000-0001-5727-2427",
        "https://orcid.org/0000-0000-0000-0000"
    ];
    private $validHyperlinkOrcids = [
        "<a href=\"https://orcid.org/0000-0003-3904-0248\">",
        "<a href=\"https://orcid.org/0000-0002-1648-966X\">"
    ];
    private $invalidHyperlinkOrcids = [
        "<a href=\"https://orcid.org/0000-0000-0000-0000\">",
        "<a href=\"orcid.org/0000-0001-5727-2427\">"
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetectsValidTextOrcids(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->validTextOrcids, $this->documentChecker->words);

        $this->assertEquals(3, $this->documentChecker->checkTextOrcidsNumber());
    }

    public function doesntDetectInvalidTextOrcids(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->invalidTextOrcids, $this->documentChecker->words);

        $this->assertEquals(0, $this->documentChecker->checkTextOrcidsNumber());
    }

    public function testDetectsValidHyperlinkOrcids(): void
    {
        $this->documentChecker->textHtml = $this->insertStringIntoTextHtml(implode(' ', $this->validHyperlinkOrcids), $this->documentChecker->textHtml);

        $this->assertEquals(2, $this->documentChecker->checkHyperlinkOrcidsNumber());
    }

    public function testDoesntDetectInvalidHyperlinkOrcids(): void
    {
        $this->documentChecker->textHtml = $this->insertStringIntoTextHtml(implode(' ', $this->invalidHyperlinkOrcids), $this->documentChecker->textHtml);

        $this->assertEquals(0, $this->documentChecker->checkHyperlinkOrcidsNumber());
    }

}
