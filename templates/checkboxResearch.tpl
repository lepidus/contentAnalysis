{fbvFormSection id="checkboxResearchDiv" title="plugins.generic.contentAnalysis.checkboxTitle" list=true required=true}
    {translate key="plugins.generic.contentAnalysis.checkboxResearchInvolvingHumansOrAnimals"}
	{fbvElement type="radio" name="researchInvolvingHumansOrAnimals" id="checkboxResearchInvolvingHumansOrAnimalsYes" value="1" label="plugins.generic.contentAnalysis.checkboxInputLabelYes" required=true}
	{fbvElement type="radio" name="researchInvolvingHumansOrAnimals" id="checkboxResearchInvolvingHumansOrAnimalsNo" value="0" label="plugins.generic.contentAnalysis.checkboxInputLabelNo" required=true}
{/fbvFormSection}

<script>
    function insertAfter(newNode, referenceNode) {ldelim}
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    {rdelim}


    const sectionSelectingDiv = document.getElementById('sectionId').parentNode.parentNode;
    const checkboxResearchDiv = document.getElementById('checkboxResearchDiv');
    insertAfter(checkboxResearchDiv, sectionSelectingDiv);
</script>