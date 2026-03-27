<template>
  <div id="statusChecklist">
    <div v-if="isLoading" class="contentAnalysisLoading">
      <PkpSpinner />
    </div>

    <div v-else-if="noGalley"></div>

    <div v-else-if="error" class="contentAnalysisError">
      <p>{{ error }}</p>
    </div>

    <div v-else id="checklistBody" :class="'checklist' + checklistData.generalStatus">
      <ChecklistDisplay :checklist-data="checklistData" :id-prefix="true" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import ChecklistDisplay from "./ChecklistDisplay.vue";

const { useLocalize } = pkp.modules.useLocalize;
const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;

const { t } = useLocalize();

const props = defineProps({
  submissionId: {
    type: [Number, String],
    required: true,
  },
});

const checklistData = ref({});
const isLoading = ref(true);
const error = ref(null);
const noGalley = ref(false);

const { apiUrl } = useUrl(`contentAnalysis/checklist/${props.submissionId}`);
const { data, fetch: fetchChecklist } = useFetch(apiUrl);

async function loadChecklistData() {
  isLoading.value = true;
  error.value = null;
  noGalley.value = false;
  try {
    await fetchChecklist();
    if (data.value?.noGalley) {
      noGalley.value = true;
    } else {
      checklistData.value = data.value || {};
    }
  } catch (e) {
    error.value = t("plugins.generic.contentAnalysis.status.loadError");
  } finally {
    isLoading.value = false;
  }
}

let hashPollInterval = null;
let lastHash = "";

function checkHash() {
  const currentHash = window.location.hash;
  if (currentHash !== lastHash) {
    lastHash = currentHash;
    if (currentHash === "#review") {
      loadChecklistData();
    }
  }
}

onMounted(() => {
  lastHash = window.location.hash;
  if (lastHash === "#review") {
    loadChecklistData();
  }
  hashPollInterval = setInterval(checkHash, 300);
});

onBeforeUnmount(() => {
  if (hashPollInterval) {
    clearInterval(hashPollInterval);
  }
});
</script>

<style>
#statusChecklist .contentAnalysisLoading {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

#statusChecklist .contentAnalysisError {
  color: #d00;
  padding: 1rem;
  background: #fee;
  border-radius: 4px;
}

#statusChecklist .analysisStatusElement {
  display: flex;
  align-items: center;
  background-color: #dcdcdc;
  padding: 8px;
  border-radius: 10px;
  margin-bottom: 18px;
}

#statusChecklist .analysisStatusElement span {
  line-height: 24px;
  margin: 0 0.5rem;
  text-align: justify;
}

#statusChecklist .analysisStatusSuccess,
#statusChecklist .analysisStatusSkipped {
  width: 1.4rem;
  height: 1.4rem;
  flex-shrink: 0;
  border-radius: 0.7rem;
  background-color: #00b28d;
}

#statusChecklist .analysisStatusWarning,
#statusChecklist .analysisStatusError,
#statusChecklist .analysisStatusUnable {
  width: 1.4rem;
  height: 1.4rem;
  flex-shrink: 0;
  border-radius: 0.7rem;
  background-color: #d00a6c;
}
</style>
