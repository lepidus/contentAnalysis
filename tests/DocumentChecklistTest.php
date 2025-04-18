<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;
use APP\plugins\generic\contentAnalysis\classes\ContentParser;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecklist;
use APP\submission\Submission;
use APP\publication\Publication;
use APP\author\Author;

class DocumentChecklistTest extends DetectionOnDocumentTest
{
    private $documentChecklist;
    private $submission;
    private $publication;
    private $publicationId = 1;
    private $title = "The curious world of the magical numbers";
    private $authorGivenName = "Sophie";
    private $authorFamilyName = "Anhalt-Zerbst";
    private $textOrcids = ["https://orcid.org/0000-0001-5727-2427", "https://orcid.org/0000-0002-1648-966X"];
    private $hyperlinkOrcids = [
        "<a href=\"https://orcid.org/0000-0001-5727-2427\">",
        "<a href=\"https://orcid.org/0000-0002-1648-966X\">"
    ];


    public function setUp(): void
    {
        parent::setUp();
        $this->documentChecklist = new DocumentChecklist($this->dummyDocumentPath);
        $this->publication = $this->createPublication();
        $this->submission = $this->createSubmission();
    }

    private function createPublication()
    {
        $publication = new Publication();
        $publication->setData('id', $this->publicationId);
        $publication->setData('title', ['en' => $this->title]);

        $publication->setData('authors', [$this->createAuthor()]);

        return $publication;
    }

    private function createSubmission()
    {
        $submission = new Submission();

        $submission->setData('currentPublicationId', $this->publicationId);
        $submission->setData('publications', [$this->publication]);

        return $submission;
    }

    private function createAuthor()
    {
        $author = new Author();
        $author->setData('givenName', $this->authorGivenName);
        $author->setData('familyName', $this->authorFamilyName);

        return $author;
    }

    private function executeChecklistAddingNewTextToDocument($text)
    {
        $parser = new ContentParser();
        $textWords = $parser->createPatternFromString($text);
        $this->documentChecklist->docChecker->words = $this->insertWordsIntoDocWordList($textWords, $this->documentChecklist->docChecker->words);
        return $this->documentChecklist->executeChecklist($this->submission);
    }

    private function getStatusChecklistTextHtmlUpdating($string)
    {
        $this->documentChecklist->docChecker->textHtml = $this->insertStringIntoTextHtml($string, $this->documentChecklist->docChecker->textHtml);
        return $this->documentChecklist->executeChecklist($this->submission);
    }

    public function testContributionSkippedSingleAuthor(): void
    {
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertEquals('Skipped', $statusChecklist['contributionStatus']);

        $this->publication->setData('authors', [$this->createAuthor(), $this->createAuthor()]);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertNotEquals('Skipped', $statusChecklist['contributionStatus']);
    }

    public function testComparisonOrcidAuthors(): void
    {
        $this->publication->setData('authors', [$this->createAuthor(), $this->createAuthor()]);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertEquals('Error', $statusChecklist['orcidStatus']);

        $statusChecklist = $this->executeChecklistAddingNewTextToDocument($this->textOrcids[0]);
        $this->assertEquals('Warning', $statusChecklist['orcidStatus']);
        $this->assertEquals('textOrcids', $statusChecklist['orcidWarningType']);

        $statusChecklist = $this->executeChecklistAddingNewTextToDocument($this->textOrcids[1]);
        $this->assertEquals('Warning', $statusChecklist['orcidStatus']);
        $this->assertEquals('hyperlinkOrcids', $statusChecklist['orcidWarningType']);

        $this->getStatusChecklistTextHtmlUpdating($this->hyperlinkOrcids[0]);
        $statusChecklist = $this->getStatusChecklistTextHtmlUpdating($this->hyperlinkOrcids[1]);
        $this->assertEquals('Success', $statusChecklist['orcidStatus']);

    }

    public function testChecksEthicOnlyWhenResearchInvolvesHumansOrAnimals(): void
    {
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertFalse(array_key_exists('ethicsCommitteeStatus', $statusChecklist));

        $this->submission->setData('researchInvolvingHumansOrAnimals', true);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertTrue(array_key_exists('ethicsCommitteeStatus', $statusChecklist));
    }

    public function testChecklistForNonArticles(): void
    {
        $this->submission->setData('nonArticle', true);
        $this->submission->setData('researchInvolvingHumansOrAnimals', true);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);

        $this->assertEquals('1', $statusChecklist['submissionIsNonArticle']);
        $this->assertTrue(array_key_exists('orcidStatus', $statusChecklist));
        $this->assertTrue(array_key_exists('titleEnglishStatus', $statusChecklist));
        $this->assertTrue(array_key_exists('dataStatementStatus', $statusChecklist));

        $this->assertFalse(array_key_exists('ethicsCommitteeStatus', $statusChecklist));
        $this->assertFalse(array_key_exists('conflictInterestStatus', $statusChecklist));
        $this->assertFalse(array_key_exists('contributionStatus', $statusChecklist));
    }

    public function testChecklistGeneralStatusWorksCorrectlyForNonArticles(): void
    {
        $this->submission->setData('nonArticle', true);
        $this->submission->setData('researchInvolvingHumansOrAnimals', true);
        $this->publication->setData('authors', [$this->createAuthor()]);

        $this->executeChecklistAddingNewTextToDocument($this->textOrcids[0]);
        $this->getStatusChecklistTextHtmlUpdating($this->hyperlinkOrcids[0]);
        $this->executeChecklistAddingNewTextToDocument('Data availability statement lorem ipsum dolor sit amet');
        $statusChecklist = $this->executeChecklistAddingNewTextToDocument($this->title);

        $this->assertEquals('1', $statusChecklist['submissionIsNonArticle']);
        $this->assertEquals('Success', $statusChecklist['orcidStatus']);
        $this->assertEquals('Success', $statusChecklist['titleEnglishStatus']);
        $this->assertEquals('Success', $statusChecklist['dataStatementStatus']);
        $this->assertEquals('Success', $statusChecklist['generalStatus']);
    }
}
