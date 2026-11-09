<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements a parser that breaks contents in a list of words
 */

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
        $pathTxt = tempnam(sys_get_temp_dir(), 'contentAnalysis_');
        if ($pathTxt === false) {
            return array();
        }

        try {
            // A command string is used because PHP 7.3, supported by this branch, does not accept an argument array
            $command = 'pdftotext -layout ' . escapeshellarg($pathFile) . ' ' . escapeshellarg($pathTxt);
            // Both paths are escaped with escapeshellarg() above
            // nosemgrep: PHPCS_SecurityAudit.BadFunctions.SystemExecFunctions.WarnSystemExec
            $process = proc_open(
                $command,
                array(
                    1 => array('file', '/dev/null', 'w'),
                    2 => array('file', '/dev/null', 'w'),
                ),
                $pipes
            );

            if (!is_resource($process)) {
                return array();
            }

            if (proc_close($process) !== 0 || !is_readable($pathTxt)) {
                return array();
            }

            $docText = file_get_contents($pathTxt);
            if ($docText === false) {
                return array();
            }
        } finally {
            if (file_exists($pathTxt)) {
                unlink($pathTxt);
            }
        }

        $docLines = preg_split("/\r\n|\n|\r/", $docText);
        $docWords = array();

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
}
