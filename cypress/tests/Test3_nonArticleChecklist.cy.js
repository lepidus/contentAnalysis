import '../support/commands.js';

describe('Content Analysis Plugin - Non-article checklist execution', function() {
    let submissionData;
    let files;

    before(function() {
        Cypress.config('defaultCommandTimeout', 4000);
        submissionData = {
            title: "Spirited Away",
			abstract: 'A girl goes on a vacation with her parents',
			keywords: ['plugin', 'testing'],
            contributors: [
                {
                    'given': 'Hayao',
                    'family': 'Miyazaki',
                    'email': 'hayao.miyazaki@ghibli.co.jp',
                    'country': 'Japan'
                }
            ]
		};
        files = [
            {
                'file': '../../plugins/generic/contentAnalysis/cypress/fixtures/documentNoPatterns.pdf',
                'fileName': 'documentNoPatterns.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Preprint Text'
            },
            {
                'file': '../../plugins/generic/contentAnalysis/cypress/fixtures/documentAllPatterns.pdf',
                'fileName': 'documentAllPatterns.pdf',
                'mimeType': 'application/pdf',
                'genre': 'Preprint Text'
            }
        ];
    });

    it('Creation of SciELO Journal role', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Users & Roles').click();
        cy.contains('button', 'Roles').click();
        cy.contains('a', 'Create New Role').click();

        cy.get('#roleId').select('Author');
        cy.get('input[name="name[en]"]').type('SciELO Journal');
        cy.contains('label', 'Role Name').click();
        cy.get('input[name="abbrev[en]"]').type('SciELO');
        cy.contains('label', 'Abbreviation').click();

        cy.get('#userGroupForm button:contains("OK")').click();
        cy.waitJQuery();
    });
    it('Assigns SciELO Journal role to user via Settings Wizard', function() {
        cy.login('admin', 'admin');
        cy.visit('index.php/index/en/admin/wizard/1');
        cy.get('button[id="users-button"]').click();
        cy.waitJQuery();

        cy.get('#userGridContainer .pkp_linkaction_search').click();
        cy.get('#userSearchForm input[name="search"]').type('eostrom', {force: true});
        cy.get('#userSearchForm .submitFormButton').click();
        cy.waitJQuery();

        cy.get('#userGridContainer .show_extras:visible').first().click();
        cy.get('#userGridContainer').contains('a', 'Edit User').click();

        cy.get('label:contains("SciELO Journal")').first().find('input').check();

        cy.get('#userDetailsForm .submitFormButton').click();
        cy.wait(2000);
    });
    it('Non-article checklist execution on PDF without any patterns', function() {
        cy.login('eostrom', null, 'publicknowledge');
        cy.createSubmission(submissionData, [files[0]]);

        cy.contains('You must select an option for the document type');

        cy.get('.pkpSteps__step__label:contains("Details")').click();
        cy.get('input[name="documentType"][value="1"]').check();
        cy.get('input[name="ethicsCouncil"][value="0"]').check();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.get('.analysisStatusElement', { timeout: 15000 }).should('exist');

        cy.assertCheckingsFailed(submissionData.title, 'nonArticle');
        cy.contains('There are one or more problems that need to be fixed before you can submit.');
        cy.contains('button', 'Submit').should('be.disabled');
    });
    it('Non-article checklist execution on PDF with all patterns', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.openIncompleteSubmission(submissionData.title);

        cy.contains('button', 'Continue').click();

        cy.get('a.show_extras').click();
        cy.get('a.pkp_linkaction_deleteGalley').first().click();
        cy.contains('button', 'OK').click();
        cy.addSubmissionGalleys([files[1]]);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.get('.analysisStatusElement', { timeout: 15000 }).should('exist');
        cy.assertCheckingsSucceeded('nonArticle');

        cy.contains('button', 'Submit').click();
        cy.get('[role="dialog"]').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});
