<?php

/**
 * @file plugins/generic/documentMetadataChecklist/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_documentMetadataChecklist
 *
 * This class implements all checks made over the PDF document file
 */

class DocumentChecker {
    private $pathFile;
    private $words;

    private $patternsContribution = array(
        array("contribuição", "dos", "autores"),
        array("contribuição", "das", "autoras"),
        array("contribuições", "dos", "autores"),
        array("contribuição", "de", "autoria"),
        array("colaborações", "individuais"),
        array("colaboração", "da", "produção", "do", "artigo"),
        array("authors", "contributions"),
        array("contribution", "of", "authority"),
        array("equal", "contribution", "as", "first", "author"),
        array("participación", "de", "los", "autores"),
        array("contribución", "de", "autores"),
        array("contribución", "de", "los", "autores"),
        array("contribuciones", "de", "los", "autores"),
        array("contribuciones", "de", "autoría"),
    );

    private $patternsConflictInterest = array(
        array("conflictos", "de", "intereses"),
        array("conflictos", "de", "interés"),
        array("conflicts", "of", "interests"),
        array("competing", "interests"),
        array("conflitos", "de", "interesses"),
    );

    private function parseDocument(){
        $pathTxt = substr($this->pathFile, 0, -3) . 'txt';
        shell_exec("pdftotext ". $this->pathFile . " " . $pathTxt . " 2>/dev/null");
        
        $text = file_get_contents($pathTxt, FILE_TEXT);
        unlink($pathTxt);
        
        for($i = 0; $i < strlen($text); $i++){ 
            while($i < strlen($text) && ctype_space($text[$i]))
                $i++;
            
            if($i < strlen($text)){
                $start = $end = $i;

                while($end < strlen($text) && !ctype_space($text[$end]))
                    $end++;
                
                $this->words[] = mb_strtolower(substr($text, $start, $end-$start));
                $i = $end;
            }
        }
    }

    function __construct($path){
        $this->pathFile = $path;
        $this->words = array();
        $this->parseDocument();
    }

    function checkAuthorsContribution(){
        for($i = 0; $i < count($this->words)-5; $i++){
            for($j = 0; $j < count($this->patternsContribution); $j++){
                $depth = 0;
                
                foreach($this->patternsContribution[$j] as $wordPattern){
                    similar_text($this->words[$i+$depth], $wordPattern, $similarity);

                    if($similarity < 75)
                        break;
                    else {
                        $depth++;
                    }
                }

                if($depth == count($this->patternsContribution[$j]))
                    return 'Success';
            }    
        }
        
        return 'Error';
    }

    function checkAuthorsORCID(){
        $orcidsDetected = array();

        for($i = 0; $i < count($this->words); $i++){
            $word = $this->words[$i];
            
            if(strlen($word) >= 19){
                $start = 0;
                while($start < strlen($word) && !ctype_digit($word[$start]))
                    $start++;

                if($start <= (strlen($word) - 19)){
                    $trecho = substr($word, $start, 19);
                    
                    if(preg_match("~\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $trecho) && !in_array($trecho, $orcidsDetected)){
                        $orcidsDetected[] = $trecho;
                    }
                }
            }
        }

        if(empty($orcidsDetected)){
            $textHtml = shell_exec("pdftohtml -s -i -stdout " . $this->pathFile . " 2>/dev/null");
            
            for($i = 0; $i < strlen($textHtml) - 37; $i++){
                $trecho = substr($textHtml, $i, 37);

                if(preg_match("~http[s]?:\/\/orcid\.org\/\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $trecho) && !in_array($trecho, $orcidsDetected)){
                    $orcidsDetected[] = $trecho;
                    $i += 37;
                }
            }
        }

        return count($orcidsDetected);
    }

    function checkConflictInterest(){
        for($i = 0; $i < count($this->words)-3; $i++){
            for($j = 0; $j < count($this->patternsConflictInterest); $j++){
                $depth = 0;
                
                foreach($this->patternsConflictInterest[$j] as $wordPattern){
                    similar_text($this->words[$i+$depth], $wordPattern, $similarity);

                    if($similarity < 75)
                        break;
                    else {
                        $depth++;
                    }
                }

                if($depth == count($this->patternsConflictInterest[$j]))
                    return 'Success';
            }    
        }

        return 'Error';
    }

    function executeChecklist($submission){
        $dataChecklist = array();

        $dataChecklist['contributionStatus'] = $this->checkAuthorsContribution();
        $dataChecklist['conflictInterestStatus'] = $this->checkConflictInterest();

        $numAuthors = count($submission->getAuthors());
        $orcidsDetected = $this->checkAuthorsORCID();
        if($orcidsDetected >= $numAuthors)
            $dataChecklist['orcidStatus'] = 'Success';
        else if($orcidsDetected > 0 && $orcidsDetected < $numAuthors) {
            $dataChecklist['orcidStatus'] = 'Warning';
            $dataChecklist['numOrcids'] = $orcidsDetected;
            $dataChecklist['numAuthors'] = $numAuthors;
        }
        else
            $dataChecklist['orcidStatus'] = 'Error';
            
        if(in_array('Error', $dataChecklist))
            $dataChecklist['generalStatus'] = 'Error';
        else if(in_array('Warning', $dataChecklist))
            $dataChecklist['generalStatus'] = 'Warning';
        else
            $dataChecklist['generalStatus'] = 'Success';

        return $dataChecklist;
    }
}