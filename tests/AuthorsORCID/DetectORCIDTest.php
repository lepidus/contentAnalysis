<?php

use PHPUnit\Framework\TestCase;

final class DetectORCIDTest extends TestCase {
    
    public function testORCIDPresent() : void {
        $pathDocument = "/home/jhon/Documentos/base_preprints/positivos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertTrue($checker->checkAuthorsORCID() > 0);
    }

    public function testORCIDNotPresent() : void{
        $pathDocument = "/home/jhon/Documentos/base_preprints/negativos/1.pdf";
        $checker = new DocumentChecker($pathDocument);

        $this->assertFalse($checker->checkAuthorsORCID() > 0);
    }
}