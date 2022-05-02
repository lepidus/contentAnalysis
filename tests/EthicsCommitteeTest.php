<?php
use PHPUnit\Framework\TestCase;
require_once ("DetectionOnDocumentTest.php");

class EthicsCommitteeTest extends DetectionOnDocumentTest {
    
    private $patternCommittee = array("aprovação","do","comitê","de","ética");

    public function setUp() : void {
        parent::setUp();
    }
    
    public function testDetection() : void {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternCommittee, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkEthicsCommittee());
    }

    public function testDoesntDetectWhenNotPresent() : void {
        $this->assertEquals("Error", $this->documentChecker->checkEthicsCommittee());
    }

}