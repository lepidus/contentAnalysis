import '../support/commands.js';

describe('Content Analysis Plugin - Ethics counsil checklist execution', function() {
    let submissionData;
    let files;

    before(function() {
        Cypress.config('defaultCommandTimeout', 10000);
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
        cy.advanceNSubmissionSteps(4);

        cy.contains('You must select an option for the Ethics Council');

        cy.get('.pkpSteps__step__label:contains("Details")').click({force: true});
        cy.get('input[name="ethicsCouncil"][value="1"]').check();
        cy.advanceNSubmissionSteps(4);
        cy.reload();
        cy.advanceNSubmissionSteps(4);

        cy.assertCheckingsFailed(submissionData.title, 'ethicsCouncil');
        cy.contains('There are one or more problems that need to be fixed before you can submit.');
        cy.contains('button', 'Submit').should('be.disabled');
    });
    it('Ethics council checklist execution on PDF with all patterns', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.openIncompleteSubmission(submissionData.title);

        cy.advanceNSubmissionSteps(1);

        cy.get('.show_extras').first().click();
        cy.get('a.pkp_linkaction_deleteGalley').first().click();
        cy.contains('button', 'OK').click();
        cy.addSubmissionGalleys([files[1]]);

        cy.advanceNSubmissionSteps(3);

        cy.reload();
        cy.advanceNSubmissionSteps(4);
        cy.assertCheckingsSucceeded('ethicsCouncil');

        cy.contains('button', 'Submit').click();
        cy.get('[role="dialog"]').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
});
