<?php

use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_orcid_autores/positivos/";
        $found = 0;

        for($i = 1; $i <= 222; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);
            $numOrcids = $checker->checkAuthorsORCID();
            
            if($numOrcids > 0)
                $found++;
        }

        $accuracy = ($found / 222);

        $this->assertEquals(1, $accuracy);
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_orcid_autores/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 178; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsORCID() == 0)
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 178);

        $this->assertEquals(1, $accuracy);
    }
}