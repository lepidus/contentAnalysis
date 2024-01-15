import '../support/commands.js';

describe('Content Analysis Plugin - Standard checklist execution', function() {
    let submissionData;
    let files;
    
    before(function() {
        Cypress.config('defaultCommandTimeout', 4000);
        submissionData = {
            title: "My Neighbor Totoro",
			abstract: 'Two girls find a big friend in the forest',
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

    it('Ethics council checklist execution on PDF without any pattern', function() {
        cy.login('eostrom', null, 'publicknowledge');
        cy.createSubmission(submissionData, [files[0]]);
        cy.reload();

        cy.contains('You must select an option for the Ethics Council');
        
        cy.get('.pkpSteps__step__label:contains("Details")').click();
        cy.get('input[name="ethicsCouncil"][value="1"]').check();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.contains('button', 'Continue').click();
        cy.reload();

        cy.assertCheckingsFailed(submissionData.title, 'ethicsCouncil');
        cy.contains('There are one or more problems that need to be fixed before you can submit.');
        cy.contains('button', 'Submit').should('be.disabled');
    });
    it('Ethics council checklist execution on PDF with all patterns', function () {
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
        cy.assertCheckingsSucceeded('ethicsCouncil');
        
        cy.contains('button', 'Submit').click();
        cy.get('.modal__panel:visible').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});
