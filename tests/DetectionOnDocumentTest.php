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

    protected function insertWordsIntoDocWordList($words, $docWordList) {
        $middlePosition = (int) count($docWordList) / 2;

        return array_merge(
            array_slice($docWordList, 0, $middlePosition),
            $words,
            array_slice($docWordList, $middlePosition)
        );
    }

}