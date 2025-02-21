function beginSubmission(submissionData) {
    cy.get('input[name="locale"][value="en"]').click();
    cy.setTinyMceContent('startSubmission-title-control', submissionData.title);
    
    cy.get('input[name="submissionRequirements"]').check();
    cy.get('input[name="privacyConsent"]').check();
    cy.contains('button', 'Begin Submission').click();
}

function detailsStep(submissionData) {
    cy.setTinyMceContent('titleAbstract-abstract-control-en', submissionData.abstract);
    submissionData.keywords.forEach(keyword => {
        cy.get('#titleAbstract-keywords-control-en').type(keyword, {delay: 0});
        cy.get('#titleAbstract-keywords-control-en').type('{enter}', {delay: 0});
    });
    
    if ('ethicsCouncil' in submissionData) {
        cy.get('input[name="ethicsCouncil"][value="' + submissionData.ethicsCouncil + '"]').check();
    }
    
    cy.contains('button', 'Continue').click();
}

function filesStep(file) {
    cy.addSubmissionGalleys(file);
    cy.contains('button', 'Continue').click();
}

function contributorsStep(submissionData) {
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
}

function assertNumberOfCheckingsPerformed(checklistType) {
    let numberCheckingForChecklistType = {
        'standard': 7,
        'ethicsCouncil': 8,
        'nonArticle': 3
    };
    
    cy.get('.analysisStatusElement').should('have.length', numberCheckingForChecklistType[checklistType]);
}

Cypress.Commands.add('createSubmission', function(submissionData, files) {
	cy.get('div#myQueue a:contains("New Submission")').click();
        
    beginSubmission(submissionData);
    detailsStep(submissionData);
    filesStep(files);
    contributorsStep(submissionData);
    cy.get('input[name="relationStatus"][value="1"]').check();
    cy.contains('button', 'Continue').click();
    cy.waitJQuery();
});

Cypress.Commands.add('findSubmission', function(tab, title) {
	cy.get('#' + tab + '-button').click();
    cy.get('.listPanel__itemSubtitle:visible:contains("' + title + '")').first()
        .parent().parent().within(() => {
            cy.get('.pkpButton:contains("View")').click();
        });
});

Cypress.Commands.add('assertCheckingsFailed', function(title, checklistType) {
	assertNumberOfCheckingsPerformed(checklistType);

    cy.get('#statusORCID').within(() => {
        cy.get('.analysisStatusError');
        cy.contains('span', "No ORCIDs were identified in the document. Make sure all the ORCIDs have been inserted in the document following the link format recommended by ORCID, also containing the hyperlink to the ORCID record page. Also make sure all the links correspond to the correct ORCID registry of each person listed in the document authorship.");
    });

    cy.get('#statusTitleEnglish').within(() => {
        cy.get('.analysisStatusError');
        cy.contains('span', "The english title \"" + title + "\" was not found in the sent PDF file. Check if paper's title is equal to the one inserted in the submission's form");
    });

    cy.get('#statusDataStatement').within(() => {
        cy.get('.analysisStatusError');
        cy.contains('span', "The data availability statement was not found in the document");
    });

    if (checklistType != 'nonArticle') {
        cy.get('#statusContribution').within(() => {
            cy.get('.analysisStatusError');
            cy.contains('span', "The author's contribution statement was not identified in the document. Make sure that a section called \"Authors contribution\" has been inserted in the document, following preferably the CRediT taxonomy to list the individual contributions.");
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
    
        if (checklistType == 'ethicsCouncil') {
            cy.get('#statusEthicsCommittee').within(() => {
                cy.get('.analysisStatusError');
                cy.contains('span', "The Ethics Committee Approval Statement was not found in the document.");
            });
        }
    }

    cy.contains('It is necessary to correct these pending issues to complete your submission');
});

Cypress.Commands.add('assertCheckingsSucceeded', function(checklistType) {
    assertNumberOfCheckingsPerformed(checklistType);

    cy.get('#statusORCID').within(() => {
        cy.get('.analysisStatusSuccess');
        cy.contains('span', "The ORCIDs of all authors were identified");
    });

    cy.get('#statusTitleEnglish').within(() => {
        cy.get('.analysisStatusSuccess');
        cy.contains('span', "The title in english was found in the document");
    });

    cy.get('#statusDataStatement').within(() => {
        cy.get('.analysisStatusSuccess');
        cy.contains('span', "The data availability statement is present in the document");
    });

    if (checklistType != 'nonArticle') {
        cy.get('#statusContribution').within(() => {
            cy.get('.analysisStatusSuccess');
            cy.contains('span', "The author's contribution statement was identified in the document");
        });

        cy.get('#statusConflictInterest').within(() => {
            cy.get('.analysisStatusSuccess');
            cy.contains('span', "The conflict of interests statement was identified in the document");
        });

        cy.get('#statusKeywordsEnglish').within(() => {
            cy.get('.analysisStatusSuccess');
            cy.contains('span', "The keywords in english were found in the document");
        });

        cy.get('#statusAbstractEnglish').within(() => {
            cy.get('.analysisStatusSuccess');
            cy.contains('span', "The abstract in english was found in the document");
        });

        if (checklistType == 'ethicsCouncil') {
            cy.get('#statusEthicsCommittee').within(() => {
                cy.get('.analysisStatusSuccess');
                cy.contains('span', "The Ethics Committee Approval Statement was found in the document");
            });
        }
    }
});