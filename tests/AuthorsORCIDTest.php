<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class AuthorsORCIDTest extends DetectionOnDocumentTest
{
    private $validTextOrcids = [
        "https://orcid.org/0000-0001-5727-2427",
        "https://orcid.org/0000-0002-1648-966X",
        "orcid.org/0000-0002-1825-0097"
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

        $numberTextOrcids = count($this->documentChecker->checkTextOrcids());

        $this->assertEquals(3, $numberTextOrcids);
    }

    public function doesntDetectInvalidTextOrcids(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->invalidTextOrcids, $this->documentChecker->words);
        $numberTextOrcids = count($this->documentChecker->checkTextOrcids());

        $this->assertEquals(0, $numberTextOrcids);
    }

    public function testDetectsValidHyperlinkOrcids(): void
    {
        $this->documentChecker->textHtml = $this->insertStringIntoTextHtml(implode(' ', $this->validHyperlinkOrcids), $this->documentChecker->textHtml);
        $numberHyperlinkOrcids = count($this->documentChecker->checkHyperlinkOrcids());

        $this->assertEquals(2, $numberHyperlinkOrcids);
    }

    public function testDoesntDetectInvalidHyperlinkOrcids(): void
    {
        $this->documentChecker->textHtml = $this->insertStringIntoTextHtml(implode(' ', $this->invalidHyperlinkOrcids), $this->documentChecker->textHtml);
        $numberHyperlinkOrcids = count($this->documentChecker->checkHyperlinkOrcids());

        $this->assertEquals(0, $numberHyperlinkOrcids);
    }

}
