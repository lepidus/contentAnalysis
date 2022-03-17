<?php
use PHPUnit\Framework\TestCase;
import ('plugins.generic.contentAnalysis.classes.DocumentChecklist');
import('classes.submission.Submission');
import('classes.publication.Publication');
import('classes.article.Author');

class DocumentChecklistTest extends TestCase {

    private $documentChecklist;
    private $submission;
    private $publication;
    private $publicationId = 1;
    private $title = "The curious world of the magical numbers";
    private $authorGivenName = "Sophie";
    private $authorFamilyName = "Anhalt-Zerbst";
    private $dummyDocumentPath;

    public function setUp() : void {
        $this->dummyDocumentPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "dummy_document.pdf";
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
}