<link rel="stylesheet" type="text/css" href="/plugins/generic/contentAnalysis/styles/statusChecklist.css">

<div id="statusChecklist">
    <div id="checklistHeader">
        {if $placedOn == 'workflow'}
            <h2>{translate key="plugins.generic.contentAnalysis.status.title"}</h2>
        {/if}
    </div>
    <div id="checklistBody" class="checklist{$generalStatus}">
        <div id="titleMessage">
            <h3 id="statusGeneral">{translate key="plugins.generic.contentAnalysis.status.message{$generalStatus}"}</h3>
        </div>
        
        <div id="statusContribution" class="element{$contributionStatus}">
            <div class="status{$contributionStatus}"></div>
            <span>{translate key="plugins.generic.contentAnalysis.status.authorsContribution{$contributionStatus}"}</span>
        </div>

        
        <div id="statusORCID" class="element{$orcidStatus}">
            <div class="status{$orcidStatus}"></div>
            {if $orcidStatus == "Warning"}
                <span>{translate key="plugins.generic.contentAnalysis.status.orcid{$orcidStatus}" numOrcids=$numOrcids numAuthors=$numAuthors}</span>
            {else}
                <span>{translate key="plugins.generic.contentAnalysis.status.orcid{$orcidStatus}"}</span>
            {/if}
        </div>

        <div id="statusConflictInterest" class="element{$conflictInterestStatus}">
            <div class="status{$conflictInterestStatus}"></div>
            <span>{translate key="plugins.generic.contentAnalysis.status.conflictInterest{$conflictInterestStatus}"}</span>
        </div>

        <div id="statusMetadataEnglish" class="element{$metadataEnglishStatus}">
            <div class="status{$metadataEnglishStatus}"></div>
            {if $metadataEnglishStatus == "Warning"}
                <span>{translate key="plugins.generic.contentAnalysis.status.metadataEnglish{$metadataEnglishStatus}" textoMetadados=$textoMetadados}</span>
            {else}
                <span>{translate key="plugins.generic.contentAnalysis.status.metadataEnglish{$metadataEnglishStatus}"}</span>
            {/if}
        </div>

        {if $generalStatus != "Success"}
            <span><div id="checklistAdvice">{translate key="plugins.generic.contentAnalysis.status.advice"}</div></span>
        {/if}
    </div>
</div>