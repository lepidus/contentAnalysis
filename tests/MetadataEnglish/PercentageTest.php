<?php

use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase {
    public function testPercentageMetadataEnglishPresent() : void {
        $mainPath = "/home/jhon/Documentos/base_metadados_ingles/positivos/";
        $foundPositives = 0;
        
        for($i = 1; $i <= 137; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkMetadataInEnglish("")['statusMetadataEnglish'] != 'Error')
                $foundPositives++;
        }

        $accuracy = ($foundPositives/137);

        $this->assertGreaterThanOrEqual(0.95, $accuracy);
    }

    public function testPercentageTitleEnglishPresent() : void {
        $mainPath = "/home/jhon/Documentos/base_metadados_ingles/apenas_titulo/";
        $foundTitle = 0;
        $titles = array(
            "Contributions of Florence Nightingale's Environmental Theory to the prevention of the COVID-19 pandemic",
            "Worker’s health and the struggle against COVID-19",
            "Preparing for the Covid-19 mental health crisis in Latin America – Using Early Evidence from Countries that Experienced Covid-19 first",
            "Nursing paradigms in times of COVID-2019",
            "Imiquimod: an old molecule against COVID-19",
            "COVID-19: PROTECTION MEASURES IN MATERNAL HEALTH",
            "Symptoms of Anxiety and depression during the outbreak of COVID-19 in Paraguay",
            "Imiquimod: an old molecule against COVID-19",
            "COVID-19 in Piauí: initial scenario and perspectives for coping",
            "Coronaviruses and rheumatic diseases, assumptions, myths and realities",
            "Information about the new coronavirus disease (COVID-19)",
            "Worker’s health and the struggle against COVID-19",
            "CHALLENGES IN THE FIGHT AGAINST THE COVID-19 PANDEMIC IN UNIVERSITY HOSPITALS",
            "COVID-19 - Computed tomography findings in two patients in Petrópolis, Rio de Janeiro, Brazil",
            "The new coronavirus and the risk to children's health",
            "Pandemic by COVID-19 and dental practice",
        );
        
        for($i = 1; $i <= 16; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkMetadataInEnglish($titles[$i-1])['title'] == 'Success')
                $foundTitle++;
        }

        $accuracy = ($foundTitle/16);

        $this->assertEquals(1, $accuracy);
    }

    public function testPercentageMetadataEnglishNotPresent() : void{
        $mainPath = "/home/jhon/Documentos/base_metadados_ingles/negativos/";
        $foundNegatives = 0;
        
        for($i = 1; $i <= 47; $i++){
            $path = $mainPath . $i . ".pdf";
            $checker = new DocumentChecker($path);

            if($checker->checkMetadataInEnglish("")['statusMetadataEnglish'] == 'Error')
                $foundNegatives++;
        }

        $accuracy = ($foundNegatives/47);

        $this->assertEquals(1, $accuracy);
    }
}