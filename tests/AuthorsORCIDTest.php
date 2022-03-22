<?php
use PHPUnit\Framework\TestCase;
require_once ("DetectionOnDocumentTest.php");

class AuthorsORCIDTest extends DetectionOnDocumentTest {
    
    private $completeOrcid = "https://orcid.org/0000-0001-5727-2427";
    private $orcidOnlyNumbers = "0000-0001-5727-2427";
    private $invalidOrcid = "https://orcid.org/0000-0000-0000-0000";
    private $invalidOrcidOnlyNumbers = "0000-0000-0000-0000";

    public function setUp() : void {
        parent::setUp();
    }
    
    public function testDetectionCompleteOrcid() : void {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->completeOrcid], $this->documentChecker->words);
        
        $this->assertEquals(1, $this->documentChecker->checkAuthorsORCID());
    }

    public function testDetectionOrcidOnlyNumbers() : void {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->orcidOnlyNumbers], $this->documentChecker->words);

        $this->assertEquals(1, $this->documentChecker->checkAuthorsORCID());
    }

    public function testDoesntDetectOrcidWhenNotPresent() : void {
        $this->assertEquals(0, $this->documentChecker->checkAuthorsORCID());
    }

    public function testDoesntConsiderInvalidOrcids(): void {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList([$this->invalidOrcid, $this->invalidOrcidOnlyNumbers], $this->documentChecker->words);
        $this->assertEquals(0, $this->documentChecker->checkAuthorsORCID());
    }
}