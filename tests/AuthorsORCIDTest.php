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

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetectsValidTextOrcids(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->validTextOrcids, $this->documentChecker->words);

        $this->assertEquals(3, $this->documentChecker->checkTextOrcidsNumber());
    }

    public function testDetectsOrcidsWithLineBreaks(): void
    {
        $orcidWithLineBreak = ['https://orcid.org/0000-0002-', '9826-4690'];
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($orcidWithLineBreak, $this->documentChecker->words);

        $this->assertEquals(1, $this->documentChecker->checkTextOrcidsNumber());
    }

    public function testDoesntDetectsRepeatedOrcids(): void
    {
        $repeatedOrcid = ['https://orcid.org/0000-0001-5727-2427', 'https://orcid.org/0000-0001-5727-2427'];
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($repeatedOrcid, $this->documentChecker->words);

        $this->assertEquals(1, $this->documentChecker->checkTextOrcidsNumber());
    }

    public function testDoesntDetectInvalidTextOrcids(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->invalidTextOrcids, $this->documentChecker->words);

        $this->assertEquals(0, $this->documentChecker->checkTextOrcidsNumber());
    }
}
