<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements all checks made over the PDF document file
 */

namespace APP\plugins\generic\contentAnalysis\classes;

use APP\plugins\generic\contentAnalysis\classes\ContentParser;

class DocumentChecker
{
    private $pathFile;
    public $words;

    public function __construct($path)
    {
        $this->pathFile = $path;
        $parser = new ContentParser();
        $this->words = $parser->parseDocument($path);
    }

    private $patternsContribution = [
        ["contribuição", "dos", "autores"],
        ["contribuição", "das", "autoras"],
        ["contribuição", "dos/das", "autores"],
        ["contribuições", "dos", "autores"],
        ["contribuições", "dos/das", "autores"],
        ["contribuição", "das/dos", "autores/as"],
        ["contribuição", "das/dos", "autoras/es"],
        ["contribuição", "de", "autoria"],
        ["colaborações", "individuais"],
        ["colaboração", "da", "produção", "do", "artigo"],
        ["authors", "contributions"],
        ["contribution", "of", "authority"],
        ["equal", "contribution", "as", "first", "author"],
        ["participación", "de", "los", "autores"],
        ["contribución", "de", "autores"],
        ["contribución", "de", "la", "autoría"],
        ["contribución", "de", "los", "autores"],
        ["contribuciones", "de", "los", "autores"],
        ["contribuciones", "de", "autoría"],
        ["as","contribuições","de","cada","autora:"],
    ];

    private function checksumOrcid($orcid)
    {
        $total = 0;
        for ($i = 0; $i < strlen($orcid) - 1; $i++) {
            $digit = (int) $orcid[$i];
            $total = ($total + $digit) * 2;
        }
        $remainder = $total % 11;
        $result = (12 - $remainder) % 11;

        $checksum = $result == 10 ? "x" : strval($result);
        return $checksum == $orcid[-1];
    }

    private function checkOrcid($text)
    {
        if (preg_match("~orcid\.org\/(\d{4}-\d{4}-\d{4}-\d{3}(\d|X|x))~", $text, $matches)) {
            $orcid = strtolower($matches[1]);
            $orcidNumbers = str_replace("-", "", $orcid);
            if ($this->checksumOrcid($orcidNumbers)) {
                return $orcid;
            }
        }

        return "";
    }

    private $patternsConflictInterest = [
        ["conflictos", "de", "intereses"],
        ["conflictos", "de", "interés"],
        ["conflicts", "of", "interests"],
        ["competing", "interests"],
        ["conﬂito", "de", "interesses"],
        ["conflitos", "de", "interesses"],
        ["Não","há","conflito","de","interesses"],
    ];

    private $patternsKeywordsEnglish = [
        ["keywords"],
        ["keyword"],
        ["descriptors:"],
        ["key", "words"],
        ["key", "words:"],
        ["palavras-chave"]
    ];

    private $patternsAbstractEnglish = [
        ["abstract"],
        ["abstract:"],
        ["summary"]
    ];

    private $patternsEthicsCommittee = [
        ["número", "de", "identificação/aprovação", "do", "cep"],
        ["parecer", "do", "cep"],
        ["comitê", "de", "ética"],
        ["comissão", "de", "ética"],
        ["conselho", "de", "ética"],
        ["câmara", "de", "ética"],
        ["comissão", "nacional", "de", "ética"],
        ["comité", "de", "ética"],
        ["ethics", "committee"],
    ];

    private $patternsDataStatement = [
        ["data", "statement"],
        ["research", "data", "availability"],
        ["data", "availability", "statement"],
        ["data", "accessibility", "statement"],
        ["disponibilidad", "de", "datos"],
        ["disponibilidade", "de", "dados"],
        ["datos", "de", "investigación"],
        ["dados", "de", "pesquisa"],
        ["dados", "da", "pesquisa"]
    ];

    private function checkForPattern($patterns, $limit, $limiarForWord, $limiarForPattern)
    {
        for ($i = 0; $i < count($this->words) - $limit; $i++) {
            for ($j = 0; $j < count($patterns); $j++) {
                $depth = 0;

                foreach ($patterns[$j] as $wordPattern) {
                    similar_text($this->words[$i + $depth], $wordPattern, $similarity);
                    if ($similarity < $limiarForWord) {
                        break;
                    } else {
                        $depth++;
                    }
                }

                if ($depth / count($patterns[$j]) >= $limiarForPattern) {
                    return 'Success';
                }
            }
        }

        return 'Error';
    }

    public function checkAuthorsContribution()
    {
        return $this->checkForPattern($this->patternsContribution, 3, 75, 1);
    }

    public function checkTextOrcidsNumber()
    {
        $orcidsDetected = [];

        for ($i = 0; $i < count($this->words) - 1; $i++) {
            $word = $this->words[$i];
            $nextWord = $this->words[$i + 1];
            $orcid = $this->checkOrcid($word.$nextWord);

            if ($orcid != '' && !in_array($orcid, $orcidsDetected)) {
                $orcidsDetected[] = $orcid;
            }
        }

        return count($orcidsDetected);
    }

    public function checkConflictInterest()
    {
        return $this->checkForPattern($this->patternsConflictInterest, 3, 75, 1);
    }

    public function checkKeywordsInEnglish()
    {
        return $this->checkForPattern($this->patternsKeywordsEnglish, 2, 92, 1);
    }

    public function checkAbstractInEnglish()
    {
        return $this->checkForPattern($this->patternsAbstractEnglish, 2, 92, 1);
    }

    public function checkTitleInEnglish($title)
    {
        if (empty($title)) {
            return 'Unable';
        }

        $parser = new ContentParser();
        $cleanedTitle = $parser->cleanStyledText($title);
        $patternTitle = $parser->createPatternFromString($cleanedTitle);

        return $this->checkForPattern(array($patternTitle), count($patternTitle), 75, 0.75);
    }

    public function checkEthicsCommittee()
    {
        return $this->checkForPattern($this->patternsEthicsCommittee, 2, 75, 1);
    }

    public function checkDataStatement()
    {
        return $this->checkForPattern($this->patternsDataStatement, 3, 90, 1);
    }
}
