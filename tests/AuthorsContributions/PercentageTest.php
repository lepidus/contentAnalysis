<?php

use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/positivos/";
        $foundPositives = 0;

        for($i = 1; $i <= 45; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsContribution() == 'Success')
                $foundPositives++;
        }

        $accuracy = ($foundPositives / 45);

        $this->assertGreaterThanOrEqual(0.75, $accuracy);   //Espera ao menos 75% de acurácia
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_preprints/negativos/";
        $foundNegatives = 0;

        for($i = 1; $i <= 150; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsContribution() == 'Error')
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives / 150);

        $this->assertEquals(1, $accuracy);   //Sem espaço para falsos positivos
    }
}