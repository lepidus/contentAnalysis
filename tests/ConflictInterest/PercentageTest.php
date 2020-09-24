<?php

use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_conflito_interesses/positivos/";
        $foundPositives = 0;

        for($i = 1; $i <= 135; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);
            
            if($checker->checkConflictInterest() == 'Success')
                $foundPositives++;
        }

        $accuracy = ($foundPositives / 135);

        $this->assertGreaterThanOrEqual(0.85, $accuracy);
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_conflito_interesses/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 265; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkConflictInterest() == 'Error')
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 265);

        $this->assertEquals(1, $accuracy);
    }
}