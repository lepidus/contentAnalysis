<?php
use PHPUnit\Framework\TestCase;
import ('plugins.generic.contentAnalysis.classes.DocumentChecker');

class DetectionOnDocumentTest extends TestCase {
    
    protected $documentChecker;
    protected $dummyDocumentPath;

    public function setUp() : void {
        $this->dummyDocumentPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "dummy_document.pdf";
        $this->documentChecker = new DocumentChecker($this->dummyDocumentPath);
    }

    protected function insertArrayIntoAnother($array, $anotherArray) {
        $midPos = (int) count($anotherArray) / 2;

        return array_merge(
            array_slice($anotherArray, 0, $midPos),
            $array,
            array_slice($anotherArray, $midPos + 1)
        );
    }

}