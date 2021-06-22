<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements all checks made over the PDF document file
 */
require __DIR__ . '/../autoload.inc.php';

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
        array("as","contribuições","de","cada","autora:"),
    );

    private function isORCID($text) {
        return !preg_match("~doi\.org~", $text) && (preg_match("~\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $text) || preg_match("~http[s]?:\/\/orcid\.org\/~", $text));
    }

    private $patternsConflictInterest = array(
        array("conflictos", "de", "intereses"),
        array("conflictos", "de", "interés"),
        array("conflicts", "of", "interests"),
        array("competing", "interests"),
        array("conflitos", "de", "interesses"),
        array("Não","há","conflito","de","interesses"),
    );

    private $patternsKeywordsEnglish = array(
        array("keywords"),
        array("keyword"),
        array("descriptors:"),
        array("key", "words"),
    );

    private $patternsAbstractEnglish = array(
        array("abstract"),
        array("abstract:"),
        array("summary")
    );

    function __construct($path){
        $this->pathFile = $path;
        $parser = new ContentParser();
        $this->words = $parser->parseDocument($path);
    }

    private function checkForPattern($patterns, $limit, $limiarForWord, $limiarForPattern){
        for($i = 0; $i < count($this->words)-$limit; $i++){
            for($j = 0; $j < count($patterns); $j++){
                $depth = 0;
                
                foreach($patterns[$j] as $wordPattern){
                    similar_text($this->words[$i+$depth], $wordPattern, $similarity);
                    if($similarity < $limiarForWord)
                        break;
                    else {
                        $depth++;
                    }
                }

                if($depth/count($patterns[$j]) >= $limiarForPattern)
                    return 'Success';
            }    
        }
        
        return 'Error';
    }

    function checkAuthorsContribution(){
        error_log(print_r($this->patternsContribution,true));
        return $this->checkForPattern($this->patternsContribution, 5, 75, 1);
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

        if(empty($orcidsDetected)){ // If nothing was detected, the ORCIDs are probably in image-link format
            $textHtml = shell_exec("pdftohtml -s -i -stdout " . $this->pathFile . " 2>/dev/null");
            
            for($i = 0; $i < strlen($textHtml) - 37; $i++){
                $textFragment = substr($textHtml, $i, 37);

                if(preg_match("~http[s]?:\/\/orcid\.org\/\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $textFragment) && !in_array($textFragment, $orcidsDetected)){
                    $orcidsDetected[] = $textFragment;
                    $i += 37;
                }
            }
        }

        return count($orcidsDetected);
    }

    function checkConflictInterest(){
        return $this->checkForPattern($this->patternsConflictInterest, 3, 75, 1);
    }

    private function checkKeywordsEnglish(){
        return $this->checkForPattern($this->patternsKeywordsEnglish, 2, 92, 1);
    }

    private function checkAbstractEnglish(){
        return $this->checkForPattern($this->patternsAbstractEnglish, 2, 95, 1);
    }

    private function checkTitleEnglish($title){
        if(!$title)
            return 'Error';

        $parser = new ContentParser();
        $patternTitle = $parser->createPatternFromString($title);
        return $this->checkForPattern(array($patternTitle), count($patternTitle), 75, 0.75);
    }

    function checkMetadataInEnglish($title){
        $status = array();
        
        $status['keywords'] = $this->checkKeywordsEnglish();
        $status['abstract'] = $this->checkAbstractEnglish();
        $status['title'] = $this->checkTitleEnglish($title);

        if(!in_array('Success', $status))
            $status['statusMetadataEnglish'] = 'Error';
        else if(in_array('Error', $status))
            $status['statusMetadataEnglish'] = 'Warning';
        else
            $status['statusMetadataEnglish'] = 'Success';

        return $status;
    }
}