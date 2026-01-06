<?php

namespace APP\plugins\generic\contentAnalysis\tests;

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecker;

abstract class DetectionOnDocumentTest extends TestCase
{
    protected const FIXTURES_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    protected $documentChecker;
    protected $dummyDocumentPath;

    public function setUp(): void
    {
        $this->dummyDocumentPath = self::FIXTURES_PATH . 'dummy_document.pdf';
        $this->documentChecker = new DocumentChecker($this->dummyDocumentPath);
    }

    protected function insertWordsIntoDocWordList($words, $docWordList)
    {
        $middlePosition = (int) (count($docWordList) / 2);

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
}
