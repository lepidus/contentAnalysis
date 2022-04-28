{fbvFormSection id="checkboxResearchDiv"}
    <input type="checkbox" name="researchInvolvingHumansOrAnimals" id="checkboxResearchInvolvingHumansOrAnimals" required="true" value="1"/>
    {translate key="plugins.generic.contentAnalysis.checkboxResearchInvolvingHumansOrAnimals"}
{/fbvFormSection}

<script>
    function insertAfter(newNode, referenceNode) {ldelim}
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    {rdelim}


    var sectionSelectingDiv = document.getElementById('sectionId').parentNode.parentNode;
    var checkboxResearchDiv = document.getElementById('checkboxResearchDiv');
    insertAfter(checkboxResearchDiv, sectionSelectingDiv);
</script>