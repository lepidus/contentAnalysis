<?php

namespace APP\plugins\generic\contentAnalysis\tests;

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecker;
use APP\plugins\generic\contentAnalysis\classes\ContentParser;

class DetectionOnDocumentTest extends TestCase
{
    protected $documentChecker;
    protected $dummyDocumentPath;

    public function setUp(): void
    {
        $this->dummyDocumentPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "dummy_document.pdf";
        $this->documentChecker = new DocumentChecker($this->dummyDocumentPath);
    }

    protected function insertWordsIntoDocWordList($words, $docWordList)
    {
        $middlePosition = (int) count($docWordList) / 2;

        return array_merge(
            array_slice($docWordList, 0, $middlePosition),
            $words,
            array_slice($docWordList, $middlePosition)
        );
    }

    protected function insertStringIntoTextHtml($string, $textHtml)
    {
        return $textHtml . " " . $string;
    }

    public function testParserRemovesLineNumbering(): void
    {
        $this->dummyDocumentPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "dummy_document_numbered.pdf";
        $this->documentChecker = new DocumentChecker($this->dummyDocumentPath);

        $expectedWordsFirstLine = ["lorem", "ipsum", "dolor", "sit", "amet,", "consectetur", "adipiscing", "elit."];
        $parsedWordsFirstLine = array_slice($this->documentChecker->words, 0, count($expectedWordsFirstLine));

        $this->assertEquals($expectedWordsFirstLine, $parsedWordsFirstLine);
    }
}
