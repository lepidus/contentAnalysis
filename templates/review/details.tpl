<div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
        <h3 id="review-plugin-content-analysis-details">
            {translate key="plugins.generic.contentAnalysis.stepSection.name"}
        </h3>
        <pkp-button
            aria-describedby="review-plugin-content-analysis-details"
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
                <notification v-if="errors.ethicsCouncil" type="warning">
                    <icon icon="exclamation-triangle" :inline="true"></icon>
                    {translate key="plugins.generic.contentAnalysis.ethicsCouncil.selected.notInformed"}
                </notification>
                <div v-if="!errors.ethicsCouncil">
                    {translate key="plugins.generic.contentAnalysis.ethicsCouncil.selected.{$ethicsCouncilSelection}"}
                </div>
            </div>
        </div>
        {if $submitterHasJournalRole}
            <div class="submissionWizard__reviewPanel__item">
                <h4 class="submissionWizard__reviewPanel__item__header">
                    {translate key="plugins.generic.contentAnalysis.documentType.label"}
                </h4>
                <div class="submissionWizard__reviewPanel__item__value">
                    <notification v-if="errors.documentType" type="warning">
                        <icon icon="exclamation-triangle" :inline="true"></icon>
                        {translate key="plugins.generic.contentAnalysis.documentType.selected.notInformed"}
                    </notification>
                    <div v-if="!errors.documentType">
                        {translate key="plugins.generic.contentAnalysis.documentType.selected.{$documentTypeSelection}"}
                    </div>
                </div>
            </div>
        {/if}
    </div>
</div>