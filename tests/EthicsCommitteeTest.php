<?php

use PHPUnit\Framework\TestCase;
use APP\plugins\generic\contentAnalysis\tests\DetectionOnDocumentTest;

class EthicsCommitteeTest extends DetectionOnDocumentTest
{
    private $patternCommittee = array("aprovação","do","comitê","de","ética");

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testDetection(): void
    {
        $this->documentChecker->words = $this->insertWordsIntoDocWordList($this->patternCommittee, $this->documentChecker->words);

        $this->assertEquals("Success", $this->documentChecker->checkEthicsCommittee());
    }

    public function testDoesntDetectWhenNotPresent(): void
    {
        $this->assertEquals("Error", $this->documentChecker->checkEthicsCommittee());
    }
}
