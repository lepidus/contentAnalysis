<?php
use PHPUnit\Framework\TestCase;
import ('plugins.generic.contentAnalysis.classes.DocumentChecker');
import ('plugins.generic.contentAnalysis.classes.ContentParser');

class DetectionOnDocumentTest extends TestCase {
    
    protected $documentChecker;
    protected $dummyDocumentPath;

    public function setUp() : void {
        $this->dummyDocumentPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "dummy_document.pdf";
        $this->documentChecker = new DocumentChecker($this->dummyDocumentPath);
    }

    protected function insertArrayIntoAnother($array, $anotherArray) {
        $middlePosition = (int) count($anotherArray) / 2;

        return array_merge(
            array_slice($anotherArray, 0, $middlePosition),
            $array,
            array_slice($anotherArray, $middlePosition)
        );
    }

}