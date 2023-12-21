import '../support/commands.js';

describe('Content Analysis Plugin - Standard checklist execution', function() {
    let submissionData;
    let files;
    
    before(function() {
        submissionData = {
            title: "Kiki's Delivery Service",
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

    function assertCheckingsFail() {
        cy.get('.analysisStatusElement').should('have.length', 6);

        cy.get('#statusContribution').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "The author's contribution statement was not identified in the document. Make sure that a section called \"Authors contribution\" has been inserted in the document, following preferably the CRediT taxonomy to list the individual contributions.");
        });

        cy.get('#statusORCID').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "No ORCIDs were identified in the document. Make sure all the ORCIDs have been inserted in the document following the link format recommended by ORCID. Also make sure all the links correspond to the correct ORCID registry of each person listed in the document authorship.");
        });

        cy.get('#statusConflictInterest').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "The conflict of interests statement was not identified in the document. Make sure that a section called \"Conflicts of interest\" has been inserted in the document. We recommend the following of the COPE guidelines for the formulation of the conflicts of interest declaration.");
        });

        cy.get('#statusKeywordsEnglish').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "The keywords in english were not found in the document");
        });

        cy.get('#statusAbstractEnglish').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "The abstract in english was not found in the document");
        });

        cy.get('#statusTitleEnglish').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "The english title \"Kiki's Delivery Service\" was not found in the sent PDF file. Check if paper's title is equal to the one inserted in the submission's form");
        });

        cy.contains('It is necessary to correct these pending issues to complete your submission');
    }

    /*it('Standard checklist execution on PDF without any pattern', function() {
        cy.login('eostrom', null, 'publicknowledge');
        cy.createSubmission(submissionData, [files[0]]);
        cy.reload();

        assertCheckingsFail();
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
    });*/
    it('Standard checklist execution on PDF with all patterns', function () {
        cy.login('eostrom', null, 'publicknowledge');
        cy.findSubmission('myQueue', submissionData.title);
        
        cy.contains('button', 'Continue').click();

        cy.get('a.show_extras').click();
        cy.get('a.pkp_linkaction_deleteGalley').click();
        cy.get('.pkp_modal_confirmation button:contains("OK")').click();
        cy.addSubmissionGalleys([files[1]]);
        cy.contains('button', 'Continue').click();

        
    });
});
