<?php

use PHPUnit\Framework\TestCase;

import('plugins.generic.contentAnalysis.classes.ContentParser');

class ContentParserTest extends TestCase
{
    private $workDir;

    public function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'contentAnalysisParserTest_' . uniqid();
        mkdir($this->workDir);
    }

    public function tearDown(): void
    {
        foreach (glob($this->workDir . DIRECTORY_SEPARATOR . '*') as $file) {
            unlink($file);
        }
        rmdir($this->workDir);
    }

    public function testParsingDoesNotRemoveDocumentStoredWithTxtExtension(): void
    {
        $documentPath = $this->workDir . DIRECTORY_SEPARATOR . 'galleyFile.txt';
        copy(__DIR__ . DIRECTORY_SEPARATOR . 'dummy_document.pdf', $documentPath);
        $originalHash = sha1_file($documentPath);

        $parsedWords = (new ContentParser())->parseDocument($documentPath);

        $this->assertFileExists($documentPath);
        $this->assertSame($originalHash, sha1_file($documentPath));
        $this->assertSame(['lorem', 'ipsum', 'dolor'], array_slice($parsedWords, 0, 3));
    }
}
