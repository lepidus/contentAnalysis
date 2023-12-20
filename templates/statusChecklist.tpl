<link rel="stylesheet" type="text/css" href="/plugins/generic/contentAnalysis/styles/statusChecklist.css">

<div id="statusChecklist">
    {if $placedOn == 'workflow'}
        <div id="checklistHeader">
            <h2>{translate key="plugins.generic.contentAnalysis.status.title"}</h2>
        </div>
    {/if}
    <div id="checklistBody" class="checklist{$generalStatus}">
        <div id="titleMessage">
            <h4 id="analysisStatusGeneral">{translate key="plugins.generic.contentAnalysis.status.message{$generalStatus}"}</h4>
        </div>
    
        {if isset($contributionStatus)}
            <div id="statusContribution" class="analysisStatusElement">
                <div class="analysisStatus{$contributionStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.authorsContribution{$contributionStatus}"}</span>
            </div>
        {/if}
        
        <div id="statusORCID" class="analysisStatusElement">
            <div class="analysisStatus{$orcidStatus}"></div>
            {if $orcidStatus == "Warning"}
                <span>{translate key="plugins.generic.contentAnalysis.status.orcid{$orcidStatus}" numOrcids=$numOrcids numAuthors=$numAuthors}</span>
            {else}
                <span>{translate key="plugins.generic.contentAnalysis.status.orcid{$orcidStatus}"}</span>
            {/if}
        </div>

        {if isset($conflictInterestStatus)}
            <div id="statusConflictInterest" class="analysisStatusElement">
                <div class="analysisStatus{$conflictInterestStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.conflictInterest{$conflictInterestStatus}"}</span>
            </div>
        {/if}

        {if isset($keywordsEnglishStatus)}
            <div id="statusKeywordsEnglish" class="analysisStatusElement">
                <div class="analysisStatus{$keywordsEnglishStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.keywordsEnglish{$keywordsEnglishStatus}"}</span>
            </div>
        {/if}

        {if isset($abstractEnglishStatus)}
            <div id="statusAbstractEnglish" class="analysisStatusElement">
                <div class="analysisStatus{$abstractEnglishStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.abstractEnglish{$abstractEnglishStatus}"}</span>
            </div>
        {/if}

        <div id="statusTitleEnglish" class="analysisStatusElement">
            <div class="analysisStatus{$titleEnglishStatus}"></div>
            {if $titleEnglishStatus == "Error"}
                <span>{translate key="plugins.generic.contentAnalysis.status.titleEnglish{$titleEnglishStatus}" titleInEnglish=$titleInEnglish}</span>
            {else}
                <span>{translate key="plugins.generic.contentAnalysis.status.titleEnglish{$titleEnglishStatus}"}</span>
            {/if}
        </div>

        {if isset($ethicsCommitteeStatus)}
            <div id="statusEthicsCommittee" class="analysisStatusElement">
                <div class="analysisStatus{$ethicsCommitteeStatus}"></div>
                <span>{translate key="plugins.generic.contentAnalysis.status.ethicsCommittee{$ethicsCommitteeStatus}"}</span>
            </div>
        {/if}

        {if $generalStatus != "Success" and $placedOn == 'submission'}
            <notification type="warning">
                <icon icon="exclamation-triangle" :inline="true"></icon>
                {translate key="plugins.generic.contentAnalysis.status.advice"}
            </notification>
        {/if}
    </div>
</div>