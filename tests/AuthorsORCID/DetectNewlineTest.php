<?php

use PHPUnit\Framework\TestCase;

final class DetectNewlineTest extends TestCase {
    public function testPercentagePositives() : void {
        $mainPath = "/home/jhon/Documentos/base_newline/";
        $numORCIDs = array(6,7,2,3,6,2,4,5,5,5);
        $foundOkay = 0;

        for($i = 1; $i <= 10; $i++){
            $path = $mainPath . "Exemplo". $i . ".pdf";
            $checker = new DocumentChecker($path);
            $numDetected = $checker->checkAuthorsORCID();
            
            if($numDetected >= $numORCIDs[$i-1])
                $foundOkay++;
        }

        $this->assertEquals(10, $foundOkay);
    }
}