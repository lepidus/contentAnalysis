<?php
use PHPUnit\Framework\TestCase;
import ('plugins.generic.contentAnalysis.classes.DocumentChecker');

class AuthorsContributionTest extends TestCase {
    
    private $documentChecker;
    private $dummyDocumentPath;
    private $patternContribution = array("contribuição", "dos", "autores");

    public function setUp() : void {
        $this->dummyDocumentPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "dummy_document.pdf";
        $this->documentChecker = new DocumentChecker($this->dummyDocumentPath);
    }

    private function insertArrayIntoAnother($array, $anotherArray) {
        $midPos = (int) count($anotherArray) / 2;

        return array_merge(
            array_slice($anotherArray, 0, $midPos),
            $array,
            array_slice($anotherArray, $midPos + 1)
        );
    }
    
    public function testDetection() : void {
        $this->documentChecker->words = $this->insertArrayIntoAnother($this->patternContribution, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkAuthorsContribution());
    }

    public function testDoesntDetectWhenNotPresent() : void {
        $this->assertEquals("Error", $this->documentChecker->checkAuthorsContribution());
    }

}