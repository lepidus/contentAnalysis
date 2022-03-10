<?php
use PHPUnit\Framework\TestCase;
import ('plugins.generic.contentAnalysis.classes.DocumentChecker');

class AuthorsORCIDTest extends TestCase {
    
    private $documentChecker;
    private $dummyDocumentPath;
    private $completeOrcid = "https://orcid.org/0000-0001-5727-2427";
    private $orcidOnlyNumbers = "0000-0001-5727-2427";

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
    
    public function testDetectionCompleteOrcid() : void {
        $this->documentChecker->words = $this->insertArrayIntoAnother([$this->completeOrcid], $this->documentChecker->words);
        
        $this->assertEquals(1, $this->documentChecker->checkAuthorsORCID());
    }

    public function testDetectionOrcidOnlyNumbers() : void {
        $this->documentChecker->words = $this->insertArrayIntoAnother([$this->orcidOnlyNumbers], $this->documentChecker->words);

        $this->assertEquals(1, $this->documentChecker->checkAuthorsORCID());
    }

    public function testDoesntDetectOrcidWhenNotPresent() : void {
        $this->assertEquals(0, $this->documentChecker->checkAuthorsORCID());
    }

}