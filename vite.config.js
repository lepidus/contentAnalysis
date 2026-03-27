import { resolve } from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import i18nExtractKeys from "./i18nExtractKeys.vite.js";

export default defineConfig({
  plugins: [
    i18nExtractKeys({
      extraKeys: [
        "plugins.generic.contentAnalysis.status.messageSuccess",
        "plugins.generic.contentAnalysis.status.messageWarning",
        "plugins.generic.contentAnalysis.status.messageError",
        "plugins.generic.contentAnalysis.status.authorsContributionSuccess",
        "plugins.generic.contentAnalysis.status.authorsContributionError",
        "plugins.generic.contentAnalysis.status.authorsContributionSkipped",
        "plugins.generic.contentAnalysis.status.orcidSuccess",
        "plugins.generic.contentAnalysis.status.orcidError",
        "plugins.generic.contentAnalysis.status.conflictInterestSuccess",
        "plugins.generic.contentAnalysis.status.conflictInterestError",
        "plugins.generic.contentAnalysis.status.keywordsEnglishSuccess",
        "plugins.generic.contentAnalysis.status.keywordsEnglishError",
        "plugins.generic.contentAnalysis.status.abstractEnglishSuccess",
        "plugins.generic.contentAnalysis.status.abstractEnglishError",
        "plugins.generic.contentAnalysis.status.titleEnglishSuccess",
        "plugins.generic.contentAnalysis.status.titleEnglishUnable",
        "plugins.generic.contentAnalysis.status.dataStatementSuccess",
        "plugins.generic.contentAnalysis.status.dataStatementError",
        "plugins.generic.contentAnalysis.status.ethicsCommitteeSuccess",
        "plugins.generic.contentAnalysis.status.ethicsCommitteeError",
      ],
    }),
    vue(),
  ],
  build: {
    target: "es2016",
    lib: {
      entry: resolve(__dirname, "resources/js/main.js"),
      name: "ContentAnalysisPlugin",
      fileName: "build",
      formats: ["iife"],
    },
    outDir: resolve(__dirname, "public/build"),
    rollupOptions: {
      external: ["vue"],
      output: {
        globals: {
          vue: "pkp.modules.vue",
        },
      },
    },
  },
});
