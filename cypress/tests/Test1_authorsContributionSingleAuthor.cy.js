function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

describe('Content Analysis Plugin - Authors contribution for single author', function() {
    it('Check if authors contribution checking is not executed when submission has a single author', function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        
        loginAdminUser();
        cy.get("#active-button").click();
        cy.get(".listPanel__item:visible > .listPanel__item--submission > .listPanel__itemSummary > .listPanel__itemActions > a").first().click();
        cy.wait(2000);
        cy.get("#publication-button").click();

        cy.get("#contributors-button").click();
        cy.get("#contributors-grid > .pkp_controllers_grid > table > tbody > tr:visible").its('length').should('eq', 1);

        cy.get("#checklistInfo-button").click();
        cy.get("#statusContribution > .statusSkipped").should('be.visible');
        cy.get("#statusContribution > span").contains("The author's contribution statement is not necessary in single authorship cases");
    });

});
