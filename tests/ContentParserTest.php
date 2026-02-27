<?php

namespace APP\plugins\generic\contentAnalysis\tests;

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\classes\ContentParser;

class ContentParserTest extends TestCase
{
    private const FIXTURES_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
    private $contentParser;

    public function setUp(): void
    {
        $this->contentParser = new ContentParser();
    }

    public function testParsingOfDocument(): void
    {
        $expectedParsedWords = [
            '"lorem',
            'ipsum',
            'dolor',
            'sit',
            'amet",',
            "'consectetur'",
            'adipiscing',
            'elit.',
            'proin',
            'arcu',
            'diam,',
            'elementum',
            'id',
            'quam',
            'id,',
            'finibus',
            'porttitor',
            'dolor.',
            'donec',
            'porta',
            'ullamcorper',
            'volutpat.'
        ];
        $parsedWords = $this->contentParser->parseDocument(self::FIXTURES_PATH . 'dummy_document.pdf');
        $parsedWords = array_slice($parsedWords, 0, count($expectedParsedWords));

        $this->assertEquals($expectedParsedWords, $parsedWords);
    }

    public function testParsingOfLineNumberedDocument(): void
    {
        $expectedParsedWords = [
            'lorem',
            'ipsum',
            'dolor',
            'sit',
            'amet,',
            'consectetur',
            'adipiscing',
            'elit.',
            'etiam',
            'ex',
            'libero,',
            'porttitor',
            'elit',
            'eget,',
            'maximus',
            'viverra',
            'arcu.'
        ];
        $parsedWords = $this->contentParser->parseDocument(self::FIXTURES_PATH . 'dummy_document_numbered.pdf');
        $parsedWords = array_slice($parsedWords, 0, count($expectedParsedWords));

        $this->assertEquals($expectedParsedWords, $parsedWords);
    }

    public function testCreatePatternFromString(): void
    {
        $string = 'Innovations and new advances for this world: a survey';
        $expectedPattern = ['innovations', 'and', 'new', 'advances', 'for', 'this', 'world:', 'a', 'survey'];

        $patternCreated = $this->contentParser->createPatternFromString($string);
        $this->assertEquals($expectedPattern, $patternCreated);
    }

    public function testCleansHtmlStylingFromTitle(): void
    {
        $styledTitle = '<b>Innovations</b> and <i>new</i> advances for <u>this world</u>: a survey';
        $expectedCleanedTitle = 'Innovations and new advances for this world: a survey';

        $cleanedTitle = $this->contentParser->cleanStyledText($styledTitle);
        $this->assertEquals($expectedCleanedTitle, $cleanedTitle);
    }

    public function testCleansOtherCharactersFromTitle(): void
    {
        $title = 'Reflections on “Arrival” and brazilian sign language (LIBRAS)';
        $expectedCleanedTitle = 'Reflections on "Arrival" and brazilian sign language (LIBRAS)';
        $cleanedTitle = $this->contentParser->cleanStyledText($title);
        $this->assertEquals($expectedCleanedTitle, $cleanedTitle);

        $title = 'Schindler’s List: ‘absolut cinema’';
        $expectedCleanedTitle = "Schindler's List: 'absolut cinema'";
        $cleanedTitle = $this->contentParser->cleanStyledText($title);
        $this->assertEquals($expectedCleanedTitle, $cleanedTitle);
    }
}
