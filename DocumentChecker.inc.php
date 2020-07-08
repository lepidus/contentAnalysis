<?php

/**
 * @file plugins/generic/documentMetadataChecklist/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_documentMetadataChecklist
 *
 * This class implements all checks made over the PDF document file
 */
use Spatie\PdfToText\Pdf;

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

    private function parseDocument(){
        $text = Pdf::getText($this->pathFile);
        
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
                    return true;
            }    
        }
        return false;
    }

    function checkAuthorsORCID(){
        for($i = 0; $i < count($this->words); $i++){
            $word = $this->words[$i];
            
            if(strlen($word) >= 19){
                $start = 0;
                while($start < strlen($word) && !ctype_digit($word[$start]))
                    $start++;

                if($start <= (strlen($word) - 19)){
                    $trecho = substr($word, $start, 19);
                    
                    if(preg_match("~\d{4}-\d{4}-\d{4}-\d{3}(\d|X)~", $trecho))
                        return true;
                }
            }
        }

        return false;
    }
}