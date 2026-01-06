<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements the checklist of the document using the DocumentChecker class to execute all of them
 */

namespace APP\plugins\generic\contentAnalysis\classes;

use APP\plugins\generic\contentAnalysis\classes\DocumentChecker;

class DocumentChecklist
{
    public $docChecker;

    public function __construct($path)
    {
        $this->docChecker = new DocumentChecker($path);
    }

    public function executeChecklist($submission)
    {
        $dataChecklist = array();
        $submissionIsArticle = !$submission->getData('nonArticle');

        if ($submissionIsArticle) {
            $dataChecklist = array_merge($dataChecklist, $this->getStatusOfArticleExclusiveCheckings($submission));
        }

        $dataChecklist = array_merge(
            $dataChecklist,
            $this->getStatusORCIDs($submission),
            $this->getTitleInEnglishStatus($submission),
            [
                'dataStatementStatus' => $this->docChecker->checkDataStatement(),
                'submissionIsNonArticle' => ($submissionIsArticle ? '0' : '1')
            ]
        );

        if (in_array('Error', $dataChecklist)) {
            $dataChecklist['generalStatus'] = 'Error';
        } elseif (in_array('Warning', $dataChecklist)) {
            $dataChecklist['generalStatus'] = 'Warning';
        } else {
            $dataChecklist['generalStatus'] = 'Success';
        }

        return $dataChecklist;
    }

    private function getStatusOfArticleExclusiveCheckings($submission)
    {
        $numAuthors = count($submission->getCurrentPublication()->getData('authors'));
        $returnData = [];

        if ($numAuthors > 1) {
            $returnData['contributionStatus'] = $this->docChecker->checkAuthorsContribution();
        } else {
            $returnData['contributionStatus'] = "Skipped";
        }

        $returnData['conflictInterestStatus'] = $this->docChecker->checkConflictInterest();
        $returnData['keywordsEnglishStatus'] = $this->docChecker->checkKeywordsInEnglish();
        $returnData['abstractEnglishStatus'] = $this->docChecker->checkAbstractInEnglish();

        if ($submission->getData('researchInvolvingHumansOrAnimals')) {
            $returnData['ethicsCommitteeStatus'] = $this->docChecker->checkEthicsCommittee();
        }

        return $returnData;
    }

    private function getStatusORCIDs($submission)
    {
        $numAuthors = count($submission->getCurrentPublication()->getData('authors'));
        $textOrcidsDetected = $this->docChecker->checkTextOrcidsNumber();

        if ($textOrcidsDetected >= $numAuthors) {
            return ['orcidStatus' => 'Success'];
        } elseif ($textOrcidsDetected > 0 && $textOrcidsDetected < $numAuthors) {
            return [
                'orcidStatus' => 'Warning',
                'numOrcids' => $textOrcidsDetected,
                'numAuthors' => $numAuthors
            ];
        } else {
            return ['orcidStatus' => 'Error'];
        }
    }

    private function getTitleInEnglishStatus($submission)
    {
        $titleInEnglish = $submission->getCurrentPublication()->getData('title')['en'];
        $titleInEnglishStatus = $this->docChecker->checkTitleInEnglish($titleInEnglish);

        return [
            'titleEnglishStatus' => $titleInEnglishStatus,
            'titleInEnglish' => $titleInEnglish
        ];
    }
}
