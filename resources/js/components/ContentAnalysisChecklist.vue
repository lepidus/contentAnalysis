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

    <ChecklistDisplay v-else :checklist-data="checklistData" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import ChecklistDisplay from "./ChecklistDisplay.vue";

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
    error.value = t("plugins.generic.contentAnalysis.status.loadError");
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
</style>
