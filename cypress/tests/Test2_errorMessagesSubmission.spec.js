function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

function submissionStep1() {
    cy.get('#sectionId').select('1');
    cy.get('#pkp_submissionChecklist > ul > li > label > input').check();
    cy.get('#privacyConsent').check();
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

function checkErrorMesssagesInStep4() {
    cy.get('#statusContribution > .statusError').should('be.visible');
    cy.get('#statusContribution > span').should(contributionSpan => {
        expect(contributionSpan).to.contain("Make sure that a section called \"Authors contribution\" has been inserted in the document, following preferably the CRediT taxonomy to list the individual contributions.");
    });

    cy.get('#statusORCID > .statusError').should('be.visible');
    cy.get('#statusORCID > span').should(orcidSpan => {
        expect(orcidSpan).to.contain("Make sure all the ORCID IDs have been inserted in the document in the correct format. Also make sure all the links correspond to the correct ORCID registry of each person listed in the document authorship.");
    });

    cy.get('#statusConflictInterest > .statusError').should('be.visible');
    cy.get('#statusConflictInterest > span').should(conflictInterestSpan => {
        expect(conflictInterestSpan).to.contain("Make sure that a section called \"Conflicts of interest\" has been inserted in the document. We recommend the following of the COPE guidelines for the formulation of the conflicts of interest declaration.");
    });

    cy.get('#statusMetadataEnglish > .statusError').should('be.visible');
    cy.get('#statusMetadataEnglish > span').should(metadataSpan => {
        expect(metadataSpan).to.contain("Title, abstract and keywords in english were not found in the document");
    });
}

describe('Content Analysis Plugin - Error messages in step 4', function() {
    it('Check if error messages shown in step 4 are in accordance with the defined', function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();

        submissionStep1();
        submissionStep2();
        submissionStep3();
        checkErrorMesssagesInStep4();
    });
});