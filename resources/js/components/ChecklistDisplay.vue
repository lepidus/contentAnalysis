<template>
  <div class="contentAnalysisBody">
    <div class="contentAnalysisGeneralStatus">
      <h4 :id="idPrefix ? 'analysisStatusGeneral' : undefined">
        {{
          t(
            "plugins.generic.contentAnalysis.status.message" +
              checklistData.generalStatus
          )
        }}
      </h4>
    </div>

    <div
      v-if="checklistData.contributionStatus"
      :id="idPrefix ? 'statusContribution' : undefined"
      class="analysisStatusElement"
    >
      <div
        :class="'analysisStatus' + checklistData.contributionStatus"
      ></div>
      <span
        v-html="
          t(
            'plugins.generic.contentAnalysis.status.authorsContribution' +
              checklistData.contributionStatus
          )
        "
      ></span>
    </div>

    <div :id="idPrefix ? 'statusORCID' : undefined" class="analysisStatusElement">
      <div :class="'analysisStatus' + checklistData.orcidStatus"></div>
      <span
        v-if="checklistData.orcidStatus === 'Warning'"
        v-html="
          t('plugins.generic.contentAnalysis.status.orcidWarning', {
            numOrcids: checklistData.numOrcids,
            numAuthors: checklistData.numAuthors,
          })
        "
      ></span>
      <span
        v-else
        v-html="
          t(
            'plugins.generic.contentAnalysis.status.orcid' +
              checklistData.orcidStatus
          )
        "
      ></span>
    </div>

    <div
      v-if="checklistData.conflictInterestStatus"
      :id="idPrefix ? 'statusConflictInterest' : undefined"
      class="analysisStatusElement"
    >
      <div
        :class="'analysisStatus' + checklistData.conflictInterestStatus"
      ></div>
      <span
        v-html="
          t(
            'plugins.generic.contentAnalysis.status.conflictInterest' +
              checklistData.conflictInterestStatus
          )
        "
      ></span>
    </div>

    <div
      v-if="checklistData.keywordsEnglishStatus"
      :id="idPrefix ? 'statusKeywordsEnglish' : undefined"
      class="analysisStatusElement"
    >
      <div
        :class="'analysisStatus' + checklistData.keywordsEnglishStatus"
      ></div>
      <span>{{
        t(
          "plugins.generic.contentAnalysis.status.keywordsEnglish" +
            checklistData.keywordsEnglishStatus
        )
      }}</span>
    </div>

    <div
      v-if="checklistData.abstractEnglishStatus"
      :id="idPrefix ? 'statusAbstractEnglish' : undefined"
      class="analysisStatusElement"
    >
      <div
        :class="'analysisStatus' + checklistData.abstractEnglishStatus"
      ></div>
      <span>{{
        t(
          "plugins.generic.contentAnalysis.status.abstractEnglish" +
            checklistData.abstractEnglishStatus
        )
      }}</span>
    </div>

    <div :id="idPrefix ? 'statusTitleEnglish' : undefined" class="analysisStatusElement">
      <div
        :class="'analysisStatus' + checklistData.titleEnglishStatus"
      ></div>
      <span v-if="checklistData.titleEnglishStatus === 'Error'">{{
        t("plugins.generic.contentAnalysis.status.titleEnglishError", {
          titleInEnglish: checklistData.titleInEnglish,
        })
      }}</span>
      <span v-else>{{
        t(
          "plugins.generic.contentAnalysis.status.titleEnglish" +
            checklistData.titleEnglishStatus
        )
      }}</span>
    </div>

    <div
      v-if="checklistData.dataStatementStatus"
      :id="idPrefix ? 'statusDataStatement' : undefined"
      class="analysisStatusElement"
    >
      <div
        :class="'analysisStatus' + checklistData.dataStatementStatus"
      ></div>
      <span>{{
        t(
          "plugins.generic.contentAnalysis.status.dataStatement" +
            checklistData.dataStatementStatus
        )
      }}</span>
    </div>

    <div
      v-if="checklistData.ethicsCommitteeStatus"
      :id="idPrefix ? 'statusEthicsCommittee' : undefined"
      class="analysisStatusElement"
    >
      <div
        :class="'analysisStatus' + checklistData.ethicsCommitteeStatus"
      ></div>
      <span>{{
        t(
          "plugins.generic.contentAnalysis.status.ethicsCommittee" +
            checklistData.ethicsCommitteeStatus
        )
      }}</span>
    </div>
  </div>
</template>

<script setup>
const { useLocalize } = pkp.modules.useLocalize;
const { t } = useLocalize();

defineProps({
  checklistData: {
    type: Object,
    required: true,
  },
  idPrefix: {
    type: Boolean,
    default: false,
  },
});
</script>

<style>
.contentAnalysisBody {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.contentAnalysisGeneralStatus h4 {
  margin-top: 0;
}

.analysisStatusElement {
  display: flex;
  align-items: center;
  background-color: #dcdcdc;
  padding: 8px;
  border-radius: 10px;
  margin-bottom: 18px;
}

.analysisStatusElement span {
  line-height: 24px;
  margin: 0 0.5rem;
  text-align: justify;
}

.analysisStatusSuccess,
.analysisStatusSkipped {
  width: 1.4rem;
  height: 1.4rem;
  flex-shrink: 0;
  border-radius: 0.7rem;
  background-color: #00b28d;
}

.analysisStatusWarning,
.analysisStatusError,
.analysisStatusUnable {
  width: 1.4rem;
  height: 1.4rem;
  flex-shrink: 0;
  border-radius: 0.7rem;
  background-color: #d00a6c;
}
</style>
