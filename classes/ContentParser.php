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
    private const ZERO_WIDTH_SPACE = "\x{200B}";
    private const MIN_WORD_LENGTH = 2;
    private const NUM_DOC_LINES_SAMPLE = 5;

    private function cleanWord($word)
    {
        $patternsToReplace = [
            '“' => '"',
            '”' => '"',
            '‘' => "'",
            '’' => "'",
        ];

        return $this->replacePatternsInText($word, $patternsToReplace);
    }

    private function parseWordsFromString($string)
    {
        $words = [];

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
                if (strlen($word) >= self::MIN_WORD_LENGTH) {
                    $words[] = $this->cleanWord($word);
                }

                $i = $wordEnd;
            }
        }

        return $words;
    }

    private function parseLine(string $line, bool $docIsNumbered)
    {
        $zeroWidthSpacePattern = '/' . self::ZERO_WIDTH_SPACE . '/u';
        $line = preg_replace($zeroWidthSpacePattern, '', $line);
        $lineWords = $this->parseWordsFromString($line);

        if ($docIsNumbered && !empty($lineWords) && is_numeric($lineWords[0])) {
            array_shift($lineWords);
        }

        return $lineWords;
    }

    public function checkDocumentIsNumbered(array $docLines): bool
    {
        for ($i = 0; $i < self::NUM_DOC_LINES_SAMPLE; $i++) {
            $parsedLine = explode(' ', $docLines[$i]);
            $firstWord = $parsedLine[0];

            if (!is_numeric($firstWord)) {
                return false;
            }
        }
        return true;
    }

    public function parseDocument($pathFile, $useRawMode = true)
    {
        $pathTxt = tempnam(sys_get_temp_dir(), 'content-analysis-');

        try {
            $this->convertPdfToText($pathFile, $pathTxt, $useRawMode);

            $docText = file_get_contents($pathTxt);
            $docLines = preg_split("/\r\n|\n|\r/", $docText);
            $docWords = [];
        } finally {
            if (file_exists($pathTxt)) {
                unlink($pathTxt);
            }
        }

        $docIsNumbered = $this->checkDocumentIsNumbered($docLines);

        foreach ($docLines as $line) {
            $docWords = array_merge($docWords, $this->parseLine($line, $docIsNumbered));
        }

        return $docWords;
    }

    private function convertPdfToText($pathFile, $pathTxt, $useRawMode)
    {
        $command = ['pdftotext'];

        if ($useRawMode) {
            $command[] = '-raw';
        }

        $command[] = $pathFile;
        $command[] = $pathTxt;

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['file', '/dev/null', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            return;
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        proc_close($process);
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

                $word = mb_strtolower(substr($string, $start, $end - $start));
                if (strlen($word) >= self::MIN_WORD_LENGTH) {
                    $pattern[] = $word;
                }

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
            '”' => '"',
            '‘' => "'",
            '’' => "'",
        ];

        return $this->replacePatternsInText($text, $patternsToReplace);
    }

    private function replacePatternsInText($text, $patterns)
    {
        foreach ($patterns as $pattern => $replacement) {
            $text = str_replace($pattern, $replacement, $text);
        }

        return $text;
    }
}
