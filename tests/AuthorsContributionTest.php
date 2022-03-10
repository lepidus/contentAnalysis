<?php
use PHPUnit\Framework\TestCase;
require_once ("DetectionOnDocumentTest.php");

class AuthorsContributionTest extends DetectionOnDocumentTest {
    
    private $patternContribution = array("contribuição", "dos", "autores");

    public function setUp() : void {
        parent::setUp();
    }
    
    public function testDetection() : void {
        $this->documentChecker->words = $this->insertArrayIntoAnother($this->patternContribution, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkAuthorsContribution());
    }

    public function testDoesntDetectWhenNotPresent() : void {
        $this->assertEquals("Error", $this->documentChecker->checkAuthorsContribution());
    }

}