function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

describe('Content Analysis Plugin - Checkbox in submission step 1', function() {
    it("Check presence of checkbox for research involving humans or animals on submission step 1", function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();
        cy.get('#checkboxResearchInvolvingHumansOrAnimalsYes');
    });

    it("Make sure the checkbox for research involving humans or animals on submission step 1 is not checked", function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();
        cy.get('#checkboxResearchInvolvingHumansOrAnimalsYes').should('not.be.checked') 
        cy.get('#checkboxResearchInvolvingHumansOrAnimalsNo').should('not.be.checked')
    });
});