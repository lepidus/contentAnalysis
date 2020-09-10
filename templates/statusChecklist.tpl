<link rel="stylesheet" type="text/css" href="/plugins/generic/documentMetadataChecklist/styles/statusChecklist.css">

<div id="statusChecklist">
    <div id="checklistHeader">
        {if $placedOn == 'workflow'}
            <h2>{translate key="plugins.generic.documentMetadataChecklist.status.title"}</h2>
        {/if}
    </div>
    <div id="checklistBody" class="checklist{$generalStatus}">
        <div id="titleMessage">
            <h3 id="statusGeneral">{translate key="plugins.generic.documentMetadataChecklist.status.message{$generalStatus}"}</h3>
        </div>
        
        <div id="statusContribution" class="element{$contributionStatus}">
            <div class="status{$contributionStatus}"></div>
            <span>{translate key="plugins.generic.documentMetadataChecklist.status.authorsContribution{$contributionStatus}"}</span>
        </div>

        {if $orcidStatus == "Warning"}
            <div id="statusORCID" class="element{$orcidStatus}">
                <div class="status{$orcidStatus}"></div>
                <span>{translate key="plugins.generic.documentMetadataChecklist.status.orcid{$orcidStatus}" numOrcids=$numOrcids numAuthors=$numAuthors}</span>
            </div>
        {else}
            <div id="statusORCID" class="element{$orcidStatus}">
                <div class="status{$orcidStatus}"></div>
                <span>{translate key="plugins.generic.documentMetadataChecklist.status.orcid{$orcidStatus}"}</span>
            </div>
        {/if}

        <div id="statusConflictInterest" class="element{$conflictInterestStatus}">
            <div class="status{$conflictInterestStatus}"></div>
            <span>{translate key="plugins.generic.documentMetadataChecklist.status.conflictInterest{$conflictInterestStatus}"}</span>
        </div>

        {if $generalStatus != "Success"}
            <span><div id="checklistAdvice">{translate key="plugins.generic.documentMetadataChecklist.status.advice"}</div></span>
        {/if}
    </div>
</div>