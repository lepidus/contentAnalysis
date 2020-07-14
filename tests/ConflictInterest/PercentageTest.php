<?php

use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/positivos/";
        $foundPositives = 0;

        for($i = 1; $i <= 72; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);
            
            if($checker->checkConflictInterest())
                $foundPositives++;
        }

        $accuracy = ($foundPositives / 72);

        $this->assertGreaterThanOrEqual(0.85, $accuracy);
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 123; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if(!$checker->checkConflictInterest())
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 123);

        $this->assertEquals(1, $accuracy);
    }
}