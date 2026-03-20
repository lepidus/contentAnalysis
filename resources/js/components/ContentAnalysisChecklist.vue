<template>
  <div class="contentAnalysisArea">
    <div class="contentAnalysisHeader">
      <h2>{{ t("plugins.generic.contentAnalysis.status.title") }}</h2>
    </div>

    <div v-if="isLoading" class="contentAnalysisLoading">
      <PkpSpinner />
    </div>

    <div v-else-if="error" class="contentAnalysisError">
      <p>{{ error }}</p>
    </div>

    <div v-else class="contentAnalysisBody">
      <div class="contentAnalysisGeneralStatus">
        <h4>
          {{
            t(
              "plugins.generic.contentAnalysis.status.message" +
                checklistData.generalStatus
            )
          }}
        </h4>
      </div>

      <!-- Authors Contribution -->
      <div
        v-if="checklistData.contributionStatus"
        class="analysisStatusElement"
      >
        <div
          :class="'analysisStatus' + checklistData.contributionStatus"
        ></div>
        <span>{{
          t(
            "plugins.generic.contentAnalysis.status.authorsContribution" +
              checklistData.contributionStatus
          )
        }}</span>
      </div>

      <!-- ORCID -->
      <div class="analysisStatusElement">
        <div :class="'analysisStatus' + checklistData.orcidStatus"></div>
        <span v-if="checklistData.orcidStatus === 'Warning'">{{
          t("plugins.generic.contentAnalysis.status.orcidWarning", {
            numOrcids: checklistData.numOrcids,
            numAuthors: checklistData.numAuthors,
          })
        }}</span>
        <span v-else>{{
          t(
            "plugins.generic.contentAnalysis.status.orcid" +
              checklistData.orcidStatus
          )
        }}</span>
      </div>

      <!-- Conflict of Interest -->
      <div
        v-if="checklistData.conflictInterestStatus"
        class="analysisStatusElement"
      >
        <div
          :class="'analysisStatus' + checklistData.conflictInterestStatus"
        ></div>
        <span>{{
          t(
            "plugins.generic.contentAnalysis.status.conflictInterest" +
              checklistData.conflictInterestStatus
          )
        }}</span>
      </div>

      <!-- Keywords English -->
      <div
        v-if="checklistData.keywordsEnglishStatus"
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

      <!-- Abstract English -->
      <div
        v-if="checklistData.abstractEnglishStatus"
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

      <!-- Title English -->
      <div class="analysisStatusElement">
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

      <!-- Data Statement -->
      <div
        v-if="checklistData.dataStatementStatus"
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

      <!-- Ethics Committee -->
      <div
        v-if="checklistData.ethicsCommitteeStatus"
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
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";

const { useLocalize } = pkp.modules.useLocalize;
const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;

const { t } = useLocalize();

const props = defineProps({
  submission: {
    type: Object,
    required: true,
  },
});

const checklistData = ref({});
const isLoading = ref(true);
const error = ref(null);

const { apiUrl } = useUrl(
  `contentAnalysis/checklist/${props.submission.id}`
);
const { data, fetch: fetchChecklist } = useFetch(apiUrl);

async function loadChecklistData() {
  isLoading.value = true;
  error.value = null;
  try {
    await fetchChecklist();
    checklistData.value = data.value || {};
  } catch (e) {
    error.value = "Failed to load checklist data";
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  loadChecklistData();
});

watch(
  () => props.submission.id,
  () => {
    loadChecklistData();
  }
);
</script>

<style scoped>
.contentAnalysisArea {
  padding: 1rem;
}

.contentAnalysisHeader {
  margin-bottom: 1.5rem;
}

.contentAnalysisHeader h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
}

.contentAnalysisLoading {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

.contentAnalysisError {
  color: #d00;
  padding: 1rem;
  background: #fee;
  border-radius: 4px;
}

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
