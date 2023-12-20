<div class="submissionWizard__reviewPanel">
    <div class="submissionWizard__reviewPanel__header">
        <h3 id="review-plugin-content-analysis-checklist">
            {translate key="plugins.generic.contentAnalysis.checklistTitle"}
        </h3>
        <pkp-button
            aria-describedby="review-plugin-content-analysis-checklist"
            class="submissionWizard__reviewPanel__edit"
            @click="openStep('{$step.id}')"
        >
            {translate key="common.edit"}
        </pkp-button>
    </div>
    <div class="submissionWizard__reviewPanel__body">
        <div class="submissionWizard__reviewPanel__item">
            {include file="../../../plugins/generic/contentAnalysis/templates/statusChecklist.tpl"}
        </div>
    </div>
</div>