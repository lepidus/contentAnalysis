<?php

use PHPUnit\Framework\TestCase;

final class DetectMetadataEnglishTest extends TestCase {
    
    public function testMetadataEnglishPresent() : void {
        $pathDocument = "/home/jhon/Documentos/base_metadados_ingles/positivos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertEquals('Success', $checker->checkMetadataInEnglish("Coping with stress in pandemic times: A booklet proposal")['statusMetadataEnglish']);
    }

    public function testMetadataEnglishNotPresent() : void{
        $pathDocument = "/home/jhon/Documentos/base_metadados_ingles/negativos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertEquals('Error', $checker->checkMetadataInEnglish("")['statusMetadataEnglish']);
    }
}