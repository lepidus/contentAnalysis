<?php

use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/positivos/";
        $numAuthors = array(4, 1, 4, 1, 2, 1, 1, 2, 6, 8, 1, 2, 1, 10, 2, 2, 1, 6, 2, 1, 2, 1, 3, 8, 4, 2, 3, 6, 5, 3, 9, 9, 1, 6, 8, 4, 1, 5, 8, 4, 4, 1, 7, 1, 6, 1, 2, 7, 5, 8, 3, 8, 3, 1, 4, 3, 3, 10, 3, 1, 1, 5, 5, 2, 3, 3, 1, 4, 2, 5, 4, 6, 3, 2, 3, 4, 6, 3, 2, 2, 1, 1, 3, 3, 1, 1, 1, 3, 3, 1, 4, 5, 4, 5);
        $foundTotal = $foundPart = 0;

        for($i = 1; $i <= 94; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);
            $numOrcids = $checker->checkAuthorsORCID();
            
            if($numOrcids > 0 && $numOrcids < $numAuthors[$i-1])
                $foundPart++;
            else if($numOrcids == $numAuthors[$i-1])
                $foundTotal++;
        }

        $accuracy = (($foundPart + $foundTotal) / 94);

        $this->assertEquals(1, $accuracy);
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 101; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsORCID() == 0)
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 101);

        $this->assertEquals(1, $accuracy);
    }
}