<?php
use PHPUnit\Framework\TestCase;
require_once ("DetectionOnDocumentTest.php");
import ('plugins.generic.contentAnalysis.classes.DocumentChecklist');
import('classes.submission.Submission');
import('classes.publication.Publication');
import('classes.article.Author');


class DocumentChecklistTest extends DetectionOnDocumentTest {

    private $documentChecklist;
    private $submission;
    private $publication;
    private $publicationId = 1;
    private $title = "The curious world of the magical numbers";
    private $authorGivenName = "Sophie";
    private $authorFamilyName = "Anhalt-Zerbst";
    private $orcids = ["https://orcid.org/0000-0001-5727-2427", "https://orcid.org/0000-0001-5412-2427"];

    public function setUp() : void {
        parent::setUp();
        $this->documentChecklist = new DocumentChecklist($this->dummyDocumentPath);
        $this->publication = $this->createPublication();
        $this->submission = $this->createSubmission();
    }

    private function createPublication() {
        $publication = new Publication();
        $publication->setData('id', $this->publicationId);
        $publication->setData('title', ['en_US' => $this->title]);

        $publication->setData('authors', [$this->createAuthor()]);

        return $publication;
    }

    private function createSubmission() {
        $submission = new Submission();

        $submission->setData('currentPublicationId', $this->publicationId);
        $submission->setData('publications', [$this->publication]);

        return $submission;
    }

    private function createAuthor() {
        $author = new Author();
        $author->setData('givenName', $this->authorGivenName);
        $author->setData('familyName', $this->authorFamilyName);

        return $author;
    }

    public function testContributionSkippedSingleAuthor(): void {
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertEquals('Skipped', $statusChecklist['contributionStatus']);

        $this->publication->setData('authors', [$this->createAuthor(), $this->createAuthor()]);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertNotEquals('Skipped', $statusChecklist['contributionStatus']);
    }

    public function testComparisonOrcidAuthors(): void {
        $this->publication->setData('authors', [$this->createAuthor(), $this->createAuthor()]);

        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertEquals('Error', $statusChecklist['orcidStatus']);

        $this->documentChecklist->docChecker->words = $this->insertArrayIntoAnother([$this->orcids[0]], $this->documentChecklist->docChecker->words);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertEquals('Warning', $statusChecklist['orcidStatus']);

        $this->documentChecklist->docChecker->words = $this->insertArrayIntoAnother([$this->orcids[1]], $this->documentChecklist->docChecker->words);
        $statusChecklist = $this->documentChecklist->executeChecklist($this->submission);
        $this->assertEquals('Success', $statusChecklist['orcidStatus']);
    }
}