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
        array("authors", "contributions"),
        array("colaborações", "individuais"),
        array("participación", "de", "los", "autores"),
        array("colaboração", "da", "produção", "do", "artigo")
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
                
                $this->words[] = strtolower(substr($text, $start, $end-$start));
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

                    if($similarity < 90)
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
}