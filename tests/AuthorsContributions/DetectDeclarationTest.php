<?php

use PHPUnit\Framework\TestCase;

final class DetectDeclarationTest extends TestCase {
    
    public function testDeclarationPresent() : void {
        $pathDocument = "/home/jhon/Documentos/base_contribuicao_autores/positivos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertEquals('Success', $checker->checkAuthorsContribution());
    }

    public function testDeclarationNotPresent() : void{
        $pathDocument = "/home/jhon/Documentos/base_contribuicao_autores/negativos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertEquals('Error', $checker->checkAuthorsContribution());
    }
}