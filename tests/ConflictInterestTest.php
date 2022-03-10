<?php
use PHPUnit\Framework\TestCase;
require_once ("DetectionOnDocumentTest.php");

class ConflictInterestTest extends DetectionOnDocumentTest {
    
    private $patternConflict = array("conflicts", "of", "interests");

    public function setUp() : void {
        parent::setUp();
    }
    
    public function testDetection() : void {
        $this->documentChecker->words = $this->insertArrayIntoAnother($this->patternConflict, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkConflictInterest());
    }

    public function testDoesntDetectWhenNotPresent() : void {
        $this->assertEquals("Error", $this->documentChecker->checkConflictInterest());
    }

}