import '../support/commands.js';

describe('Content Analysis Plugin - Standard checklist execution', function() {
    let submissionData;
    let files;
    
    before(function() {
        Cypress.config('defaultCommandTimeout', 4000);
        submissionData = {
            title: "Kikis Delivery Service",
			abstract: 'A young witch starting life in her new city',
			keywords: ['plugin', 'testing'],
            ethicsCouncil: '0',
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

    it('Standard checklist execution on PDF without any pattern', function() {
        cy.login('eostrom', null, 'publicknowledge');
        cy.createSubmission(submissionData, [files[0]]);
        cy.reload();

        cy.assertCheckingsFailed(submissionData.title, 'standard');
        cy.contains('There are one or more problems that need to be fixed before you can submit.');
        cy.contains('button', 'Submit').should('be.disabled');
    });
    it('Authors contribution statement checking is skipped on single author submissions', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.get('.listPanel__itemTitle:visible:contains("Hayao Miyazaki")')
            .parent().parent().within(() => {
                cy.contains('button', 'Delete').click();
            });
        cy.contains('button', 'Delete Contributor').click();
        cy.waitJQuery();

        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.get('#statusContribution').within(() => {
            cy.get('.analysisStatusSkipped');
            cy.contains('span', "The author's contribution statement is not necessary in single authorship cases");
        });
    });
    it('Standard checklist execution on PDF with all patterns', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);
        
        cy.contains('button', 'Continue').click();

        cy.get('a.show_extras').click();
        cy.get('a.pkp_linkaction_deleteGalley').click();
        cy.get('.pkp_modal_confirmation button:contains("OK")').click();
        cy.addSubmissionGalleys([files[1]]);
        cy.contains('button', 'Continue').click();

        submissionData.contributors.forEach(authorData => {
            cy.contains('button', 'Add Contributor').click();
            cy.get('input[name="givenName-en"]').type(authorData.given, {delay: 0});
            cy.get('input[name="familyName-en"]').type(authorData.family, {delay: 0});
            cy.get('input[name="email"]').type(authorData.email, {delay: 0});
            cy.get('select[name="country"]').select(authorData.country);
            
            cy.get('.modal__panel:contains("Add Contributor")').find('button').contains('Save').click();
            cy.waitJQuery();
        });
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();

        cy.reload();
        cy.assertCheckingsSucceeded('standard');
        
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it('Checklist execution on workflow', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);

        cy.contains('button', 'Document verification').click();
        cy.assertCheckingsSucceeded('standard');
    });
});
