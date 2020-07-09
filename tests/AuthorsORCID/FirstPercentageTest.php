<?php

use PHPUnit\Framework\TestCase;

final class FirstPercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/positivos/";
        $foundPositives = 0;

        for($i = 1; $i <= 92; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsORCID())
                $foundPositives++;
        }

        $accuracy = ($foundPositives / 92);

        $this->assertGreaterThanOrEqual(0.80, $accuracy);   //Espera ao menos 80% de acurácia
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 103; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if(!$checker->checkAuthorsORCID())
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 103);

        $this->assertEquals(1, $accuracy);   //Sem espaço para falsos positivos
    }
}