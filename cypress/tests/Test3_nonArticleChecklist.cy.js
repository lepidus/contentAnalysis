import '../support/commands.js';

describe('Content Analysis Plugin - Standard checklist execution', function() {
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
        cy.contains('Users & Roles').click();
        cy.contains('button', 'Roles').click();
        cy.contains('a', 'Create New Role').click();

        cy.get('#roleId').select('Author');
        cy.get('input[name="name[en]"]').type('SciELO Journal');
        cy.contains('label', 'Role Name').click();
        cy.get('input[name="abbrev[en]"]').type('SciELO');
        cy.contains('label', 'Abbreviation').click();

        cy.get('#userGroupForm button:contains("OK")').click();
        cy.waitJQuery();

        cy.contains('span', 'SciELO Journal')
            .parent().parent().parent()
            .within(() => {
                cy.get('input[type="checkbox"]').check();
            });
    });
    it('Assigns SciELO Journal role to user', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.contains('Users & Roles').click();
        cy.contains('a', 'Search').click();
        cy.get('input[name="search"]').type('eostrom');
        cy.contains('button', 'Search').click();
        cy.waitJQuery();
        
        cy.get('.show_extras:visible').click();
        cy.contains('a', 'Edit User').click();

        cy.get('label:contains("SciELO Journal")').within(() => {
            cy.get('input').check();
        });

        cy.get('#userDetailsForm .submitFormButton').click();
        cy.waitJQuery();
    });
    it('Non-article checklist execution on PDF without any patterns', function() {
        cy.login('eostrom', null, 'publicknowledge');
        cy.createSubmission(submissionData, [files[0]]);
        cy.reload();

        cy.contains('You must select an option for the document type');

        cy.get('.pkpSteps__step__label:contains("Details")').click();
        cy.get('input[name="documentType"][value="1"]').check();
        cy.get('input[name="ethicsCouncil"][value="0"]').check();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.assertCheckingsFailed(submissionData.title, 'nonArticle');
        cy.contains('There are one or more problems that need to be fixed before you can submit.');
        cy.contains('button', 'Submit').should('be.disabled');
    });
    it('Non-article checklist execution on PDF with all patterns', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);
        
        cy.contains('button', 'Continue').click();

        cy.get('a.show_extras').click();
        cy.get('a.pkp_linkaction_deleteGalley').click();
        cy.get('.pkp_modal_confirmation button:contains("OK")').click();
        cy.addSubmissionGalleys([files[1]]);
        
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.reload();
        cy.assertCheckingsSucceeded('nonArticle');
        
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});