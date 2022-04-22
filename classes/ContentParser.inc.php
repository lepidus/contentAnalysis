<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements a parser that breaks contents in a list of words
 */

class ContentParser {
    public function parseDocument($pathFile){
        $pathTxt = substr($pathFile, 0, -3) . 'txt';
        shell_exec("pdftotext ". $pathFile . " " . $pathTxt . " -layout 2>/dev/null");
        
        $text = file_get_contents($pathTxt, FILE_TEXT);
        $words = array();
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
                    $words[] = $word;
                }
                $i = $end;
            }
        }

        return $words;
    }

    public function createPatternFromString($string){
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
}