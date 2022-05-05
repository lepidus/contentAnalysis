<?php

/**
 * @file plugins/generic/contentAnalysis/classes/DocumentChecker.inc.php
 *
 * @class DocumentChecker
 * @ingroup plugins_generic_contentAnalysis
 *
 * This class implements the checklist of the document using the DocumentChecker class to execute all of them
 */
require __DIR__ . '/../autoload.inc.php';

class DocumentChecklist {
    public $docChecker;

    function __construct($path){
        $this->docChecker = new DocumentChecker($path);
    }

    public function executeChecklist($submission){
        $dataChecklist = array();
        $submissionIsNonArticle = $submission->getData('nonArticle');

        if(!$submissionIsNonArticle) {
            $dataChecklist = array_merge($dataChecklist, $this->getStatusOfArticleChecks($submission));  
        }
        
        $dataChecklist = array_merge($dataChecklist, $this->getStatusORCIDs($submission));    
        $dataChecklist = array_merge($dataChecklist, $this->getMetadataEnglishStatus($submission, $submissionIsNonArticle));

        if(in_array('Error', $dataChecklist))
            $dataChecklist['generalStatus'] = 'Error';
        else if(in_array('Warning', $dataChecklist))
            $dataChecklist['generalStatus'] = 'Warning';
        else
            $dataChecklist['generalStatus'] = 'Success';

        return $dataChecklist;
    }

    private function getStatusOfArticleChecks($submission) {
        $numAuthors = count($submission->getAuthors());
        $returnData = [];
        
        if($numAuthors > 1) {
            $returnData['contributionStatus'] = $this->docChecker->checkAuthorsContribution();
        }
        else {
            $returnData['contributionStatus'] = "Skipped";
        }

        $returnData['conflictInterestStatus'] = $this->docChecker->checkConflictInterest();

        if($submission->getData('researchInvolvingHumansOrAnimals')) {
            $returnData['ethicsCommitteeStatus'] = $this->docChecker->checkEthicsCommittee();
        }

        return $returnData;
    }

    private function getStatusORCIDs($submission) {
        $numAuthors = count($submission->getAuthors());
        $orcidsDetected = $this->docChecker->checkAuthorsORCID();
        if($orcidsDetected >= $numAuthors)
            return ['orcidStatus' => 'Success'];
        else if($orcidsDetected > 0 && $orcidsDetected < $numAuthors) {
            return [
                'orcidStatus' => 'Warning',
                'numOrcids' => $orcidsDetected,
                'numAuthors' => $numAuthors
            ];
        }
        else return ['orcidStatus' => 'Error'];
    }

    private function getMetadataEnglishStatus($submission, $submissionIsNonArticle) {
        $titleEnglish = $submission->getCurrentPublication()->getData('title')['en_US'];
        $metaMetadata = $this->docChecker->checkMetadataInEnglish($titleEnglish, $submissionIsNonArticle);
        $returnData = [];

        if($metaMetadata['statusMetadataEnglish'] == 'Warning') {
            $returnData['textMetadata'] = $this->getMissingMetadataText($metaMetadata);
        }
        $returnData['metadataEnglishStatus'] = $metaMetadata['statusMetadataEnglish'];

        return $returnData;
    }

    private function getMissingMetadataText($metaMetadata) {
        $metadataList = array('title', 'abstract', 'keywords');
        $textMetadata = "";
        foreach ($metadataList as $metadata) {
            if($metaMetadata[$metadata] == "Error") {
                if($textMetadata != "")
                    $textMetadata .= ", ";
                $textMetadata .= __("common." . $metadata);
            }
        }
        
        return $textMetadata;
    }

}