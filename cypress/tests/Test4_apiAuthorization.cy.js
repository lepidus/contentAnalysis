describe('Content Analysis Plugin - Authorization of the API endpoints', function() {
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

    function getChecklist(submissionId) {
        return cy.request({
            url: apiUrl + '/contentAnalysis/checklist/' + submissionId,
            method: 'GET',
            failOnStatusCode: false
        });
    }

    it('Allows the submitter and denies other authors', function() {
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
            });
            getChecklist(submissionId).its('status').should('eq', 200);
        });

        cy.logout();
        cy.login('ccorino', null, 'publicknowledge');
        cy.getCsrfToken();
        cy.get('@submissionId').then((submissionId) => {
            cy.get('@csrfToken').then((csrfToken) => {
                saveForm(submissionId, csrfToken, {ethicsCouncil: '0'})
                    .its('status').should('be.oneOf', [401, 403]);
            });
            getChecklist(submissionId).its('status').should('be.oneOf', [401, 403]);
        });
    });
});
