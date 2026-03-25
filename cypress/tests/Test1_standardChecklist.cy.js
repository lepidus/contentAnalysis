import '../support/commands.js';

describe('Content Analysis Plugin - Standard checklist execution', function() {
    let submissionData;
    let files;

    before(function() {
        Cypress.config('defaultCommandTimeout', 10000);
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
        cy.get('.analysisStatusElement', { timeout: 15000 }).should('exist');

        cy.assertCheckingsFailed(submissionData.title, 'standard');
        cy.contains('There are one or more problems that need to be fixed before you can submit.');
        cy.contains('button', 'Submit').should('be.disabled');
    });
    it('Authors contribution statement checking is skipped on single author submissions', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.openIncompleteSubmission(submissionData.title);

        cy.advanceNSubmissionSteps(2);

        cy.get('.listPanel__item:contains("Hayao Miyazaki")').within(() => {
            cy.contains('button', 'Delete').click();
        });
        cy.get('div[role=dialog]').find('button').contains('Delete Contributor').click();
        cy.waitJQuery();

        cy.advanceNSubmissionSteps(2);
        cy.get('#statusContribution', { timeout: 15000 }).should('exist');

        cy.get('#statusContribution').within(() => {
            cy.get('.analysisStatusSkipped');
            cy.contains('span', "The author's contribution statement is not necessary in single authorship cases");
        });
    });
    it('Standard checklist execution on PDF with all patterns', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.openIncompleteSubmission(submissionData.title);

        cy.advanceNSubmissionSteps(1);

        cy.get('.show_extras').first().click();
        cy.get('a.pkp_linkaction_deleteGalley').first().click();
        cy.contains('button', 'OK').click();
        cy.addSubmissionGalleys([files[1]]);
        cy.advanceNSubmissionSteps(1);

        submissionData.contributors.forEach(authorData => {
            cy.contains('button', 'Add Contributor').click();
            cy.wait(1000);
            cy.get('.pkpFormField:contains("Given Name")').find('input[name*="givenName-en"]').type(authorData.given, {delay: 0});
            cy.get('.pkpFormField:contains("Family Name")').find('input[name*="familyName-en"]').type(authorData.family, {delay: 0});
            cy.get('.pkpFormField:contains("Email")').find('input').type(authorData.email, {delay: 0});
            cy.get('.pkpFormField:contains("Country")').find('select').select(authorData.country);

            cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
            cy.wait(2000);
        });
        cy.advanceNSubmissionSteps(2);
        cy.get('.analysisStatusElement', { timeout: 15000 }).should('exist');
        cy.assertCheckingsSucceeded('standard');

        cy.contains('button', 'Submit').click();
        cy.get('[role="dialog"]').within(() => {
            cy.contains('button', 'Submit').click();
        });
        cy.waitJQuery();
        cy.contains('h1', 'Submission complete');
    });
    it('Checklist execution on workflow', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.openSubmission('Active submissions', submissionData.title);

        cy.openWorkflowMenu('Document verification');

        cy.get('.analysisStatusElement').should('have.length', 7);
        cy.contains('span', "The ORCIDs of all authors were identified");
        cy.contains('span', "The title in english was found in the document");
        cy.contains('span', "The data availability statement is present in the document");
        cy.contains('span', "The author's contribution statement was identified in the document");
        cy.contains('span', "The conflict of interests statement was identified in the document");
        cy.contains('span', "The keywords in english were found in the document");
        cy.contains('span', "The abstract in english was found in the document");
    });
});
