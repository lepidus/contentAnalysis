<link rel="stylesheet" type="text/css" href="/plugins/generic/documentMetadataChecklist/styles/statusChecklist.css">

<div id="statusChecklist">
    <div id="checklistHeader">
        {if $placedOn == 'workflow'}
            <h2>{translate key="plugins.generic.documentMetadataChecklist.status.title"}</h2>
        {/if}
    </div>
    <div id="checklistBody" class="checklist{$generalStatus}">
        <div id="titleMessage">
            <img id="faceStatusGeneral" src="/plugins/generic/documentMetadataChecklist/assets/carinha{$generalStatus}.png">
            <h3 id="statusGeneral">{translate key="plugins.generic.documentMetadataChecklist.status.message{$generalStatus}"}</h3>
        </div>
        
        <div id="statusContribution" class="element{$contributionStatus}">{translate key="plugins.generic.documentMetadataChecklist.status.authorsContribution{$contributionStatus}"}</div>

        {if $orcidStatus == "Warning"}
            <div id="statusORCID" class="element{$orcidStatus}">{translate key="plugins.generic.documentMetadataChecklist.status.orcid{$orcidStatus}" numOrcids=$numOrcids numAuthors=$numAuthors}</div>
        {else}
            <div id="statusORCID" class="element{$orcidStatus}">{translate key="plugins.generic.documentMetadataChecklist.status.orcid{$orcidStatus}"}</div>
        {/if}

        <div id="statusConflictInterest" class="element{$conflictInterestStatus}">{translate key="plugins.generic.documentMetadataChecklist.status.conflictInterest{$conflictInterestStatus}"}</div>

        {if $generalStatus != "Success"}
            <div id="checklistAdvice">{translate key="plugins.generic.documentMetadataChecklist.status.advice"}</div>
        {/if}
    </div>
</div>