function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

function submissionStep1() {
    cy.get('#sectionId').select('1');
    cy.get('#checkboxResearchInvolvingHumansOrAnimals').check()
    cy.get('#checkboxNonArticleSubmission').check()
    cy.get('#pkp_submissionChecklist > ul > li > label > input').check();
    cy.get('#privacyConsent').check();
    cy.get('.checkbox_and_radiobutton > li > label:visible').contains('Author').within(() => {
        cy.get('input').check();
    });

    cy.get('#submissionStep1 > .formButtons > .submitFormButton').click();
}

function submissionStep2() {
    cy.get('.pkp_linkaction_addGalley').click();
    cy.wait(2000);
    cy.get('input[name="label"]').type('PDF', { delay: 0 });
    cy.get('#articleGalleyForm > .formButtons > .submitFormButton').click();
    cy.get('#genreId').select('1');
    cy.fixture('dummy.pdf', 'base64').then(fileContent => {
        cy.get('input[type="file"]').upload({ fileContent, 'fileName': 'dummy_document.pdf', 'mimeType': 'application/pdf', 'encoding': 'base64' });
    });
    cy.get('#continueButton').click();
    cy.get('#continueButton').click();
    cy.get('#continueButton').click();
    cy.get('#submitStep2Form > .formButtons > .submitFormButton').click();
}

function addContributor() {
    cy.get('a[id^="component-grid-users-author-authorgrid-addAuthor-button-"]').click();
    cy.wait(250);
    cy.get('input[id^="givenName-en_US-"]').type("John", {delay: 0});
    cy.get('input[id^="familyName-en_US-"]').type("Smith", {delay: 0});
    cy.get('select[id=country]').select("Reino Unido");
    cy.get('input[id^="email"]').type("john.smith@lepidus.com.br", {delay: 0});
    cy.get('label').contains("Author").click();
    cy.get('#editAuthor > .formButtons > .submitFormButton').click();
}

function submissionStep3() {
    cy.get('input[name^="title"]').first().type("Submissions title", { delay: 0 });
    cy.get('label').contains('Title').click();
    cy.get('textarea[id^="abstract-en_US"]').then(node => {
        cy.setTinyMceContent(node.attr('id'), "Example of abstract");
    });
    addContributor();
    cy.get('ul[id^="en_US-keywords-"]').then(node => {
        node.tagit('createTag', "Dummy keyword");
    });
    cy.get('#submitStep3Form > .formButtons > .submitFormButton').click();
}

function checkChecksPerformedForNonArticles() {
    cy.get('#statusORCID > .statusError').should('be.visible');

    cy.get('#statusMetadataEnglish > .statusError').should('be.visible');
    cy.get('#statusMetadataEnglish > span').should(metadataSpan => {
        expect(metadataSpan).to.contain("The title in english was not found in the document");
    });

    cy.get('#statusConflictInterest').should('not.exist');
    cy.get('#statusContribution').should('not.exist');
    cy.get('#statusEthicsCommittee').should('not.exist');
}

describe('Content Analysis Plugin - Checks performed for non-article submissions', function() {
    it("Checks what checks are performed when a submission is set as non-article", function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();

        submissionStep1();
        submissionStep2();
        submissionStep3();
        checkChecksPerformedForNonArticles();
    });
});