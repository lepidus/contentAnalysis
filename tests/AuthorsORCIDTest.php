<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class AuthorsORCIDTest extends DetectionOnDocumentTest
{
    private $completeOrcid = "https://orcid.org/0000-0001-5727-2427";
    private $orcidWithoutT = "htps://orcid.org/0000-0001-5727-2427";
    private $orcidOnlyNumbers = "0000-0001-5727-2427";
    private $invalidOrcid = "https://orcid.org/0000-0000-0000-0000";

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetectionCompleteOrcid(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->completeOrcid], $this->documentChecker->words);

        $this->assertEquals(1, $this->documentChecker->checkOrcidsNumber());
    }

    public function testDetectsOrcidWithoutT(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->orcidWithoutT], $this->documentChecker->words);

        $this->assertEquals(1, $this->documentChecker->checkOrcidsNumber());
    }

    public function testDoesntDetectsOrcidOnlyNumbers(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->orcidOnlyNumbers], $this->documentChecker->words);

        $this->assertEquals(0, $this->documentChecker->checkOrcidsNumber());
    }

    public function testDoesntDetectOrcidWhenNotPresent(): void
    {
        $this->assertEquals(0, $this->documentChecker->checkOrcidsNumber());
    }

    public function testDoesntConsiderInvalidOrcid(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->invalidOrcid], $this->documentChecker->words);
        $this->assertEquals(0, $this->documentChecker->checkOrcidsNumber());
    }
}
