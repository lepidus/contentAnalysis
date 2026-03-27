import ContentAnalysisChecklist from "./components/ContentAnalysisChecklist.vue";
import ContentAnalysisWizardChecklist from "./components/ContentAnalysisWizardChecklist.vue";

pkp.registry.registerComponent(
  "ContentAnalysisChecklist",
  ContentAnalysisChecklist
);

pkp.registry.registerComponent(
  "ContentAnalysisWizardChecklist",
  ContentAnalysisWizardChecklist
);

pkp.registry.storeExtend("workflow", (piniaContext) => {
  const workflowStore = piniaContext.store;
  const { useLocalize } = pkp.modules.useLocalize;
  const { t } = useLocalize();

  workflowStore.extender.extendFn("getMenuItems", (menuItems, args) => {
    return [
      ...menuItems,
      {
        key: "contentAnalysis",
        label: t("plugins.generic.contentAnalysis.status.title"),
        state: { primaryMenuItem: "contentAnalysis" },
      },
    ];
  });

  workflowStore.extender.extendFn("getPrimaryItems", (primaryItems, args) => {
    if (args?.selectedMenuState?.primaryMenuItem === "contentAnalysis") {
      return [
        {
          component: "ContentAnalysisChecklist",
          props: { submission: args.submission },
        },
      ];
    }
    return primaryItems;
  });
});
