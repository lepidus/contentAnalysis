<?php

use PHPUnit\Framework\TestCase;

final class FirstPercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_contribuicao_autores/positivos/";
        $countPositives = 0;

        for($i = 1; $i <= 5; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsContribution() == 'Success')
                $countPositives++;
        }

        $this->assertEquals(5, $countPositives);
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/base_contribuicao_autores/negativos/";
        $countNegatives = 0;

        for($i = 1; $i <= 5; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsContribution() == 'Error')
                $countNegatives++;
        }

        $this->assertEquals(5, $countNegatives);
    }
}