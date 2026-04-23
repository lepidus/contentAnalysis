<?php

namespace APP\plugins\generic\contentAnalysis\tests;

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecker;

class DetectionOnDocumentTest extends TestCase
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

    public function testCheckerHasWords(): void
    {
        $this->assertNotNull($this->documentChecker->words);
        $this->assertNotEmpty($this->documentChecker->words);

        $this->assertNotNull($this->documentChecker->secondaryWords);
        $this->assertNotEmpty($this->documentChecker->secondaryWords);
    }

    public function generalPatternDetectionOnPrimaryWords(): void
    {
        $pattern = ['expected', 'pattern', 'to', 'be', 'found'];
        $backupSecondaryWords = $this->documentChecker->secondaryWords;

        $checkResult = $this->documentChecker->checkForPatterns([$pattern], 5, 50, 1);
        $this->assertEquals('Error', $checkResult);

        $this->documentChecker->secondaryWords = $this->insertWordsIntoDocWordList($pattern, $this->documentChecker->secondaryWords);
        $checkResult = $this->documentChecker->checkForPatterns([$pattern], 5, 50, 1);
        $this->assertEquals('Success', $checkResult);

        $this->documentChecker->secondaryWords = $backupSecondaryWords;
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($pattern, $this->documentChecker->words);
        $checkResult = $this->documentChecker->checkForPatterns([$pattern], 5, 50, 1);
        $this->assertEquals('Success', $checkResult);
    }
}
