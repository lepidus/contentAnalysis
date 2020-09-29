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

    private function isORCID($texto) {
        return !preg_match("~doi\.org~", $texto) && (preg_match("~\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $texto) || preg_match("~http[s]?:\/\/orcid\.org\/~", $texto));
    }

    private $patternsConflictInterest = array(
        array("conflictos", "de", "intereses"),
        array("conflictos", "de", "interés"),
        array("conflicts", "of", "interests"),
        array("competing", "interests"),
        array("conflitos", "de", "interesses"),
    );

    private $patternsKeywordsEnglish = array(
        array("keywords"),
        array("keyword"),
        array("key", "words"),
    );

    private $patternsAbstractEnglish = array(
        array("abstract"),
        array("summary")
    );

    private function createPatternFromString($string){
        $pattern = array();

        for($i = 0; $i < strlen($string); $i++){ 
            while($i < strlen($string) && ctype_space($string[$i]))
                $i++;
            
            if($i < strlen($string)){
                $start = $end = $i;

                while($end < strlen($string) && !ctype_space($string[$end]))
                    $end++;
                
                $pattern[] = mb_strtolower(substr($string, $start, $end-$start));
                $i = $end;
            }
        }

        return $pattern;
    }

    private function parseDocument(){
        $pathTxt = substr($this->pathFile, 0, -3) . 'txt';
        shell_exec("pdftotext ". $this->pathFile . " " . $pathTxt . " -layout 2>/dev/null");
        
        $text = file_get_contents($pathTxt, FILE_TEXT);
        unlink($pathTxt);
        
        for($i = 0; $i < strlen($text); $i++){ 
            while($i < strlen($text) && ctype_space($text[$i]))
                $i++;
            
            if($i < strlen($text)){
                $start = $end = $i;

                while($end < strlen($text) && !ctype_space($text[$end]))
                    $end++;
                
                $word = mb_strtolower(substr($text, $start, $end-$start));
                
                if(strlen($word) >= 4 || !is_numeric($word)) {
                    $this->words[] = $word;
                }
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

        for($i = 0; $i < count($this->words)-1; $i++){
            $word = $this->words[$i];
            $nextWord = $this->words[$i+1];
                    
            if($this->isORCID($word) && !in_array($word, $orcidsDetected)) {
                $orcidsDetected[] = $word;
                $i++;
            }
            else if($this->isORCID($word.$nextWord) && !in_array($word.$nextWord, $orcidsDetected)) {
                $orcidsDetected[] = $word.$nextWord;
                $i++;
            }
        }

        if(empty($orcidsDetected)){ //Se nada foi detectado, provavelmente os ORCIDs estão no formato de imagem-link
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

    private function checkKeywordsEnglish(){
        for($i = 0; $i < count($this->words)-2; $i++){
            for($j = 0; $j < count($this->patternsKeywordsEnglish); $j++){
                $depth = 0;
                
                foreach($this->patternsKeywordsEnglish[$j] as $wordPattern){
                    similar_text($this->words[$i+$depth], $wordPattern, $similarity);

                    if($similarity < 90)
                        break;
                    else {
                        $depth++;
                    }
                }

                if($depth == count($this->patternsKeywordsEnglish[$j]))
                    return 'Success';
            }
        }

        return 'Error';
    }

    private function checkAbstractEnglish(){
        for($i = 0; $i < count($this->words)-2; $i++){
            for($j = 0; $j < count($this->patternsAbstractEnglish); $j++){
                $depth = 0;
                
                foreach($this->patternsAbstractEnglish[$j] as $wordPattern){
                    similar_text($this->words[$i+$depth], $wordPattern, $similarity);

                    if($similarity < 95)
                        break;
                    else {
                        $depth++;
                    }
                }

                if($depth == count($this->patternsAbstractEnglish[$j]))
                    return 'Success';
            }
        }

        return 'Error';
    }

    private function checkTitleEnglish($patternTitle){
        for($i = 0; $i < count($this->words)-count($patternTitle); $i++){
            for($j = 0; $j < count($patternTitle); $j++){
                $depth = 0;
                
                foreach($patternTitle[$j] as $wordPattern){
                    similar_text($this->words[$i+$depth], $wordPattern, $similarity);

                    if($similarity < 75)
                        break;
                    else {
                        $depth++;
                    }
                }

                if($depth == count($patternTitle[$j]))
                    return 'Success';
            }
        }

        return 'Error';
    }

    function checkMetadataInEnglish($title){
        $status = array();
        
        $status['keywords'] = $this->checkKeywordsEnglish();
        $status['abstract'] = $this->checkAbstractEnglish();
        if($title){
            $patternTitle = $this->createPatternFromString($title);
            $status['title'] = $this->checkTitleEnglish(array($patternTitle));
        }
        else{
            $status['title'] = 'Error';
        }

        if(!in_array('Success', $status))
            $status['statusMetadataEnglish'] = 'Error';
        else if(in_array('Error', $status))
            $status['statusMetadataEnglish'] = 'Warning';
        else
            $status['statusMetadataEnglish'] = 'Success';

        return $status;
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