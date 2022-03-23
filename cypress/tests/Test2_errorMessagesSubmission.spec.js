function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

function submissionStep1() {
    cy.get('#sectionId').select('1');
    cy.get('#pkp_submissionChecklist > ul > li > label > input').check({multiple: true});
    cy.get('#privacyConsent').check();
    cy.get('button.submitFormButton').click();
}

function submissionStep2() {
    cy.get('a:contains("Add galley")').click();
    cy.get('input[name="label"]').type('PDF', { delay: 0 });
    cy.get('#articleGalleyForm > .formButtons > .submitFormButton').click();
    cy.get('#genreId').select('1');
    dummyDocument = 'dummy_document.pdf';
    cy.fixture(dummyDocument).then(fileContent => {
        cy.get('input[type="file"').upload({fileContent, dummyDocument, mimetype: 'application/pdf'});
    });
}

describe('Content Analysis Plugin - Authors contribution for single author', function() {
    it('Check if authors contribution checking is not executed when submission has a single author', function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();

        submissionStep1();
        submissionStep2();
    });
});
