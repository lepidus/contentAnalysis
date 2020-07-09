<?php

use PHPUnit\Framework\TestCase;

final class FirstPercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/positivos/";
        $foundPositives = 0;

        for($i = 1; $i <= 94; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsORCID())
                $foundPositives++;
        }

        $accuracy = ($foundPositives / 94);

        $this->assertEquals(1, $accuracy);   //Espera ao menos 80% de acurácia
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 101; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if(!$checker->checkAuthorsORCID())
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 101);

        $this->assertEquals(1, $accuracy);   //Sem espaço para falsos positivos
    }
}