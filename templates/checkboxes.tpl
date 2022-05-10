{fbvFormSection id="checkboxResearchDiv" title="plugins.generic.contentAnalysis.checkboxTitle" list=true required=true}
    {translate key="plugins.generic.contentAnalysis.checkboxResearchInvolvingHumansOrAnimals"}
	{fbvElement type="radio" name="researchInvolvingHumansOrAnimals" id="checkboxResearchInvolvingHumansOrAnimalsYes" value="1" label="common.yes" required=true}
	{fbvElement type="radio" name="researchInvolvingHumansOrAnimals" id="checkboxResearchInvolvingHumansOrAnimalsNo" value="0" label="common.no" required=true}
{/fbvFormSection}

{if $submitterHasJournalRole}
    {fbvFormSection id="checkboxNonArticleDiv"}
        <input type="checkbox" name="nonArticle" id="checkboxNonArticleSubmission" value="1"/>
        {translate key="plugins.generic.contentAnalysis.checkboxNonArticleSubmission"}
    {/fbvFormSection}
{/if}

<script>
    function insertAfter(newNode, referenceNode) {ldelim}
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    {rdelim}

    const sectionSelectingDiv = document.getElementById('sectionId').parentNode.parentNode;
    const checkboxResearchDiv = document.getElementById('checkboxResearchDiv');
    insertAfter(checkboxResearchDiv, sectionSelectingDiv);
    
    {if $submitterHasJournalRole}
        const checkboxNonArticleDiv = document.getElementById('checkboxNonArticleDiv');
        insertAfter(checkboxNonArticleDiv, checkboxResearchDiv);
    {/if}
</script>
