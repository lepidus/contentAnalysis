<?php

use PHPUnit\Framework\TestCase;

final class FirstPercentageTest extends TestCase {

    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/amostra_preprints/positives/";
        $countPositives = 0;

        for($i = 1; $i <= 5; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkAuthorsContribution() == 'Success')
                $countPositives++;
        }

        $this->assertGreaterThanOrEqual(4, $countPositives);
    }

    public function testPercentageNegatives() : void {
        $mainPath = "/home/jhon/Documentos/amostra_preprints/negatives/";
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