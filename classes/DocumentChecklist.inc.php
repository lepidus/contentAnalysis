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

        $numAuthors = count($submission->getAuthors());
        if($numAuthors > 1) {
            $dataChecklist['contributionStatus'] = $this->docChecker->checkAuthorsContribution();
        }
        else {
            $dataChecklist['contributionStatus'] = "Skipped";
        }

        $dataChecklist['conflictInterestStatus'] = $this->docChecker->checkConflictInterest();

        $orcidsDetected = $this->docChecker->checkAuthorsORCID();
        if($orcidsDetected >= $numAuthors)
            $dataChecklist['orcidStatus'] = 'Success';
        else if($orcidsDetected > 0 && $orcidsDetected < $numAuthors) {
            $dataChecklist['orcidStatus'] = 'Warning';
            $dataChecklist['numOrcids'] = $orcidsDetected;
            $dataChecklist['numAuthors'] = $numAuthors;
        }
        else
            $dataChecklist['orcidStatus'] = 'Error';
        
        $titleEnglish = $submission->getCurrentPublication()->getData('title')['en_US'];
        $metaMetadata = $this->docChecker->checkMetadataInEnglish($titleEnglish);
        if($metaMetadata['statusMetadataEnglish'] == 'Warning') {
            $metadataList = array('title', 'abstract', 'keywords');
            $textMetadata = "";
            foreach ($metadataList as $metadata) {
                if($metaMetadata[$metadata] == "Error") {
                    if($textMetadata != "")
                        $textMetadata .= ", ";
                    $textMetadata .= __("common." . $metadata);
                }
            }
            $dataChecklist['textMetadata'] = $textMetadata;
        }
        $dataChecklist['metadataEnglishStatus'] = $metaMetadata['statusMetadataEnglish'];

        if(in_array('Error', $dataChecklist))
            $dataChecklist['generalStatus'] = 'Error';
        else if(in_array('Warning', $dataChecklist))
            $dataChecklist['generalStatus'] = 'Warning';
        else
            $dataChecklist['generalStatus'] = 'Success';

        return $dataChecklist;
    }
}