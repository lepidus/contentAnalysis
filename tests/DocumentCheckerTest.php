<?php

use PHPUnit\Framework\TestCase;

import('plugins.generic.contentAnalysis.classes.DocumentChecker');
import('plugins.generic.contentAnalysis.classes.ContentParser');

class DocumentCheckerTest extends TestCase
{
    private $workDir;

    public function setUp(): void
    {
        $this->workDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'contentAnalysis checker ' . uniqid();
        mkdir($this->workDir);
    }

    public function tearDown(): void
    {
        foreach (glob($this->workDir . DIRECTORY_SEPARATOR . '*') as $file) {
            unlink($file);
        }
        rmdir($this->workDir);
    }

    public function testExtractsHtmlFromPathWithShellSpecialCharacters(): void
    {
        $documentPath = $this->workDir . DIRECTORY_SEPARATOR . "galley's file.pdf";
        copy(__DIR__ . DIRECTORY_SEPARATOR . 'dummy_document.pdf', $documentPath);

        $documentChecker = new DocumentChecker($documentPath);

        $this->assertFileExists($documentPath);
        $this->assertStringContainsString('Lorem', (string) $documentChecker->textHtml);
    }
}
