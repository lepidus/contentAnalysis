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
    public $words;

    function __construct($path){
        $this->pathFile = $path;
        $parser = new ContentParser();
        $this->words = $parser->parseDocument($path);
    }
    
    private $patternsContribution = array(
        array("contribuição", "dos", "autores"),
        array("contribuição", "das", "autoras"),
        array("contribuição", "dos/das", "autores"),
        array("contribuições", "dos", "autores"),
        array("contribuições", "dos/das", "autores"),
        array("contribuição", "das/dos", "autores/as"),
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

    private function checksumOrcid($orcid) {
        $total = 0;
        for ($i = 0; $i < strlen($orcid)-1; $i++) {
            $digit = (int) $orcid[$i];
            $total = ($total + $digit) * 2;
        }
        $remainder = $total % 11;
        $result = (12 - $remainder) % 11;

        $checksum = $result == 10 ? "x" : strval($result);
        return $checksum == $orcid[-1];
    }

    private function isORCID($text) {
        if(!preg_match("~doi\.org~", $text) && (preg_match("~\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $text) || preg_match("~http[s]?:\/\/orcid\.org\/~", $text))) {
            preg_match("~\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x)~", $text, $matches);
            $orcid = str_replace("-", "", $matches[0]);
            return $this->checksumOrcid($orcid);
        }
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

    private $patternsEthicsCommittee = array(
        array("número", "de", "identificação/aprovação", "do", "cep"),
        array("aprovação", "no", "comitê", "de", "ética"),
        array("aprovação", "do", "comitê", "de", "ética"),
        array("aprovação", "pelo", "comitê", "de", "ética"),
        array("aprovado", "pelo", "comitê", "de", "ética"),
        array("apresentado", "ao", "comitê", "de", "ética"),
        array("submetido", "ao", "comitê", "de", "ética"),
        array("autorização", "do", "comitê", "de", "ética"),
        array("aprovado", "por", "um", "comitê", "de", "ética"),
        array("aprovação", "de", "um", "comitê", "de", "ética"),
        array("aprovação", "prévia", "de", "um", "comitê", "de", "ética"),
        array("aprovação", "do", "conselho", "de", "ética"),
        array("parecer", "comitê", "de", "ética"),
        array("comissão", "nacional", "de", "ética", "em", "pesquisa"),
        array("pelo", "comitê", "de", "ética"),
        array("câmara", "de", "ética"),
        array("aprobación", "del", "comité", "de", "ética"),
        array("aprobado", "por", "el", "comité", "de", "ética"),
        array("ethics", "committee", "approval"),
        array("from", "the", "ethics", "committee")
    );

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

    function checkMetadataInEnglish($title, $submissionIsNonArticle = false){
        $status = array();
        
        $status['title'] = $this->checkTitleEnglish($title);
        if($submissionIsNonArticle) {
            $status['statusMetadataEnglish'] = $status['title'];
        }
        else {
            $status['keywords'] = $this->checkKeywordsEnglish();
            $status['abstract'] = $this->checkAbstractEnglish();
            
            if(!in_array('Success', $status))
                $status['statusMetadataEnglish'] = 'Error';
            else if(in_array('Error', $status))
                $status['statusMetadataEnglish'] = 'Warning';
            else
                $status['statusMetadataEnglish'] = 'Success';
        }

        return $status;
    }

    function checkEthicsCommittee(){
        return $this->checkForPattern($this->patternsEthicsCommittee, 6, 75, 1);
    }
}