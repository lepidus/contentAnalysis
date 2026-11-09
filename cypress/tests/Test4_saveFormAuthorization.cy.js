describe('Content Analysis Plugin - Authorization of the saveForm endpoint', function() {
    const apiUrl = '/index.php/publicknowledge/api/v1';

    function saveForm(submissionId, csrfToken, body) {
        return cy.request({
            url: apiUrl + '/contentAnalysis/saveForm/' + submissionId,
            method: 'PUT',
            headers: {
                'X-Csrf-Token': csrfToken
            },
            body: body,
            failOnStatusCode: false
        });
    }

    it('Allows the submitter and denies other authors and invalid values', function() {
        cy.login('eostrom', null, 'publicknowledge');
        cy.getCsrfToken();
        cy.get('@csrfToken').then((csrfToken) => {
            cy.request({
                url: apiUrl + '/submissions',
                method: 'POST',
                headers: {
                    'X-Csrf-Token': csrfToken
                },
                body: {sectionId: 1}
            }).then((xhr) => {
                cy.wrap(xhr.body.id).as('submissionId');
            });
        });

        cy.get('@submissionId').then((submissionId) => {
            cy.get('@csrfToken').then((csrfToken) => {
                saveForm(submissionId, csrfToken, {ethicsCouncil: '1'})
                    .its('status').should('eq', 200);
                saveForm(submissionId, csrfToken, {ethicsCouncil: '<b>2</b>'})
                    .its('status').should('eq', 400);
                saveForm(submissionId, csrfToken, {ethicsCouncil: '0', documentType: 'other'})
                    .its('status').should('eq', 400);
            });
        });

        cy.logout();
        cy.login('ccorino', null, 'publicknowledge');
        cy.getCsrfToken();
        cy.get('@submissionId').then((submissionId) => {
            cy.get('@csrfToken').then((csrfToken) => {
                saveForm(submissionId, csrfToken, {ethicsCouncil: '0'})
                    .its('status').should('eq', 403);
            });
        });
    });
});
