<link rel="stylesheet" type="text/css" href="/plugins/generic/contentAnalysis/styles/statusChecklist.css">

<div id="statusChecklist">
    <div id="checklistHeader">
        <h2>{translate key="plugins.generic.contentAnalysis.checklistTitle"}</h2>
        {if $placedOn == 'workflow'}
            <h2>{translate key="plugins.generic.contentAnalysis.status.title"}</h2>
        {/if}
    </div>
    <div id="checklistBody" class="checklist{$generalStatus}">
        <div id="titleMessage">
            <h3 id="statusGeneral">{translate key="plugins.generic.contentAnalysis.status.message{$generalStatus}"}</h3>
        </div>
    
        {if isset($contributionStatus)}
            <div id="statusContribution" class="element{$contributionStatus}">
                <div class="status{$contributionStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.authorsContribution{$contributionStatus}"}</span>
            </div>
        {/if}
        
        <div id="statusORCID" class="element{$orcidStatus}">
            <div class="status{$orcidStatus}"></div>
            {if $orcidStatus == "Warning"}
                <span>{translate key="plugins.generic.contentAnalysis.status.orcid{$orcidStatus}" numOrcids=$numOrcids numAuthors=$numAuthors}</span>
            {else}
                <span>{translate key="plugins.generic.contentAnalysis.status.orcid{$orcidStatus}"}</span>
            {/if}
        </div>

        {if isset($conflictInterestStatus)}
            <div id="statusConflictInterest" class="element{$conflictInterestStatus}">
                <div class="status{$conflictInterestStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.conflictInterest{$conflictInterestStatus}"}</span>
            </div>
        {/if}

        <div id="statusMetadataEnglish" class="element{$metadataEnglishStatus}">
            <div class="status{$metadataEnglishStatus}"></div>
            {if $submissionIsNonArticle}
                <span>{translate key="plugins.generic.contentAnalysis.status.metadataEnglish{$metadataEnglishStatus}NonArticle"}</span>
            {else}
                {if $metadataEnglishStatus == "Warning"}
                    <span>{translate key="plugins.generic.contentAnalysis.status.metadataEnglish{$metadataEnglishStatus}" textMetadata=$textMetadata}</span>
                {else}
                    <span>{translate key="plugins.generic.contentAnalysis.status.metadataEnglish{$metadataEnglishStatus}"}</span>
                {/if}
            {/if}
        </div>

        {if isset($ethicsCommitteeStatus)}
            <div id="statusEthicsCommittee" class="element{$ethicsCommitteeStatus}">
                <div class="status{$ethicsCommitteeStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.ethicsCommittee{$ethicsCommitteeStatus}"}</span>
            </div>
        {/if}

        {if $generalStatus != "Success"}
            <span><div id="checklistAdvice">{translate key="plugins.generic.contentAnalysis.status.advice"}</div></span>
        {/if}
    </div>
</div>