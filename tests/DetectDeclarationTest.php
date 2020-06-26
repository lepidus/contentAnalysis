<?php

use PHPUnit\Framework\TestCase;

final class DetectDeclarationTest extends TestCase {
    
    public function testDeclarationPresent() : void {
        $pathDocument = "/home/jhon/Documentos/amostra_preprints/case_positive.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertTrue($checker->checkAuthorsContribution());
    }

    public function testDeclarationNotPresent() : void{
        $pathDocument = "/home/jhon/Documentos/amostra_preprints/case_negative.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertFalse($checker->checkAuthorsContribution());
    }
}