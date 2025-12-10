<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements a parser that breaks contents in a list of words
 */

namespace APP\plugins\generic\contentAnalysis\classes;

class ContentParser
{
    private function parseWordsFromString($string)
    {
        $words = array();

        for ($i = 0; $i < strlen($string); $i++) {
            while ($i < strlen($string) && ctype_space($string[$i])) {
                $i++;
            }

            if ($i < strlen($string)) {
                $wordStart = $wordEnd = $i;

                while ($wordEnd < strlen($string) && !ctype_space($string[$wordEnd])) {
                    $wordEnd++;
                }

                $word = mb_strtolower(substr($string, $wordStart, $wordEnd - $wordStart));

                $words[] = $word;
                $i = $wordEnd;
            }
        }

        return $words;
    }

    private function parseLine($line)
    {
        $lineWords = $this->parseWordsFromString($line);

        if (!empty($lineWords) && is_numeric($lineWords[0])) {
            array_shift($lineWords);
        }

        return $lineWords;
    }

    public function parseDocument($pathFile)
    {
        $pathTxt = substr($pathFile, 0, -3) . 'txt';
        shell_exec("pdftotext " . $pathFile . " " . $pathTxt . " -layout 2>/dev/null");

        $docText = file_get_contents($pathTxt);
        $docLines = preg_split("/\r\n|\n|\r/", $docText);
        $docWords = array();
        unlink($pathTxt);

        foreach ($docLines as $line) {
            $docWords = array_merge($docWords, $this->parseLine($line));
        }

        return $docWords;
    }

    public function createPatternFromString($string)
    {
        $pattern = array();

        for ($i = 0; $i < strlen($string); $i++) {
            while ($i < strlen($string) && ctype_space($string[$i])) {
                $i++;
            }

            if ($i < strlen($string)) {
                $start = $end = $i;

                while ($end < strlen($string) && !ctype_space($string[$end])) {
                    $end++;
                }

                $pattern[] = mb_strtolower(substr($string, $start, $end - $start));
                $i = $end;
            }
        }

        return $pattern;
    }

    public function cleanStyledText($text)
    {
        $patternsToReplace = [
            '<b>' => '',
            '</b>' => '',
            '<i>' => '',
            '</i>' => '',
            '<u>' => '',
            '</u>' => '',
            '“' => '"',
            '”' => '"'
        ];

        foreach ($patternsToReplace as $pattern => $replacement) {
            $text = str_replace($pattern, $replacement, $text);
        }

        return $text;
    }
}
