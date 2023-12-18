<div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
        <h3 id="review-plugin-content-analysis">
            {translate key="plugins.generic.contentAnalysis.stepSection.name"}
        </h3>
        <pkp-button
            aria-describedby="review-plugin-content-analysis"
            class="submissionWizard__reviewPanel__edit"
            @click="openStep('{$step.id}')"
        >
            {translate key="common.edit"}
        </pkp-button>
    </div>
    <div class="submissionWizard__reviewPanel__body">
        <div class="submissionWizard__reviewPanel__item">
            <h4 class="submissionWizard__reviewPanel__item__header">
                {translate key="plugins.generic.contentAnalysis.ethicsCouncil.label"}
            </h4>
            <div class="submissionWizard__reviewPanel__item__value">
                {translate key="plugins.generic.contentAnalysis.ethicsCouncil.selected.{$ethicsCouncilSelection}"}
            </div>
        </div>
        {if $submitterHasJournalRole}
            <div class="submissionWizard__reviewPanel__item">
                <h4 class="submissionWizard__reviewPanel__item__header">
                    {translate key="plugins.generic.contentAnalysis.documentType.label"}
                </h4>
                <div class="submissionWizard__reviewPanel__item__value">
                    {translate key="plugins.generic.contentAnalysis.documentType.selected.{$documentTypeSelection}"}
                </div>
            </div>
        {/if}
    </div>
</div>