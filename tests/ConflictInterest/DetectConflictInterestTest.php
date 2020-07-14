<?php

use PHPUnit\Framework\TestCase;

final class DetectConflictInterestTest extends TestCase {
    
    public function testConflictInterestPresent() : void {
        $pathDocument = "/home/jhon/Documentos/base_preprints/positivos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertTrue($checker->checkAuthorsORCID());
    }

    public function testConflictInterestNotPresent() : void{
        $pathDocument = "/home/jhon/Documentos/base_preprints/negativos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertFalse($checker->checkAuthorsORCID());
    }
}