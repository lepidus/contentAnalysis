function loginAdminUser() {
    cy.get('input[id=username]').click();
    cy.get('input[id=username]').type(Cypress.env('OJSAdminUsername'), { delay: 0 });
    cy.get('input[id=password]').click();
    cy.get('input[id=password]').type(Cypress.env('OJSAdminPassword'), { delay: 0 });
    cy.get('button[class=submit]').click();
}

function submissionStep1(locale) {
    cy.get('#locale').select(locale);
    cy.get('#sectionId').select('1');
    cy.get('#checkboxResearchInvolvingHumansOrAnimalsYes').check();
    cy.get('#pkp_submissionChecklist > ul > li > label > input').check();
    cy.get('#privacyConsent').check();
    cy.get('.checkbox_and_radiobutton > li > label:visible').contains('Author').within(() => {
        cy.get('input').check();
    });

    cy.get('#submissionStep1 > .formButtons > .submitFormButton').click();
}

function submissionStep2() {
    cy.get('.pkp_linkaction_addGalley').click();
    cy.wait(2000);
    cy.get('input[name="label"]').type('PDF', { delay: 0 });
    cy.get('#articleGalleyForm > .formButtons > .submitFormButton').click();
    cy.get('#genreId').select('1');
    cy.fixture('dummy.pdf', 'base64').then(fileContent => {
        cy.get('input[type="file"]').upload({ fileContent, 'fileName': 'dummy_document.pdf', 'mimeType': 'application/pdf', 'encoding': 'base64' });
    });
    cy.get('#continueButton').click();
    cy.get('input[name^="name"]').first().click();
    cy.get('input[name^="name[en_US]"]').type('dummy_document.pdf');
    cy.get('input[name^="name[es_ES]"]').type('dummy_document.pdf');
    cy.get('input[name^="name[pt_BR]"]').type('dummy_document.pdf');
    cy.get('#fileMetaData > .section > label').click();
    cy.get('#continueButton').click();
    cy.get('#continueButton').click();
    cy.get('#submitStep2Form > .formButtons > .submitFormButton').click();
}

function addContributor() {
    cy.get('a[id^="component-grid-users-author-authorgrid-addAuthor-button-"]').click();
    cy.wait(250);
    cy.get('input[id^="givenName"]').first().type("John", {delay: 0});
    cy.get('input[id^="familyName"]').first().type("Smith", {delay: 0});
    cy.get('select[id=country]').select("Brasil");
    cy.get('input[id^="email"]').type("john.smith@lepidus.com.br", {delay: 0});
    cy.get('label').contains("Author").click();
    cy.get('#editAuthor > .formButtons > .submitFormButton').click();
}

function submissionStep3() {
    cy.get('input[name^="title"]').first().type("Submissions title", { delay: 0 });
    cy.get('label').contains('Title').click();
    cy.get('textarea[id^="abstract-"').then((node) => {
        cy.setTinyMceContent(node.attr("id"), "Example of abstract");
    });
    cy.get('.section > label:visible').first().click();
    addContributor();
    cy.get('ul[id*="-keywords-"]').first().then(node => {
        node.tagit('createTag', "Dummy keyword");
    });
    cy.get('#submitStep3Form > .formButtons > .submitFormButton').click();
}

function checkErrorMesssagesInStep4() {
    cy.get('#statusContribution > .analysisStatusError').should('be.visible');
    cy.get('#statusContribution > span').should(contributionSpan => {
        expect(contributionSpan).to.contain("The author's contribution statement was not identified in the document. Make sure that a section called \"Authors contribution\" has been inserted in the document, following preferably the CRediT taxonomy to list the individual contributions.");
    });

    cy.get('#statusORCID > .analysisStatusError').should('be.visible');
    cy.get('#statusORCID > span').should(orcidSpan => {
        expect(orcidSpan).to.contain("No ORCIDs were identified in the document. Make sure all the ORCID IDs have been inserted in the document in the correct format. Also make sure all the links correspond to the correct ORCID registry of each person listed in the document authorship.");
    });

    cy.get('#statusConflictInterest > .analysisStatusError').should('be.visible');
    cy.get('#statusConflictInterest > span').should(conflictInterestSpan => {
        expect(conflictInterestSpan).to.contain("The conflict of interests statement was not identified in the document. Make sure that a section called \"Conflicts of interest\" has been inserted in the document. We recommend the following of the COPE guidelines for the formulation of the conflicts of interest declaration.");
    });

    cy.get('#statusKeywordsEnglish > .analysisStatusError').should('be.visible');
    cy.get('#statusKeywordsEnglish > span').should(keywordsSpan => {
        expect(keywordsSpan).to.contain("The keywords in english were not found in the document");
    });

    cy.get('#statusAbstractEnglish > .analysisStatusError').should('be.visible');
    cy.get('#statusAbstractEnglish > span').should(abstractSpan => {
        expect(abstractSpan).to.contain("The abstract in english was not found in the document");
    });

    cy.get('#statusTitleEnglish > .analysisStatusError').should('be.visible');
    cy.get('#statusTitleEnglish > span').should(titleSpan => {
        expect(titleSpan).to.contain("The english title \"Submissions title\" was not found in the sent PDF file. Check if paper's title is equal to the one inserted in the submission's form");
    });

    cy.get('#statusEthicsCommittee > .analysisStatusError').should('be.visible');
    cy.get('#statusEthicsCommittee > span').should(ethicsCommitteeSpan => {
        expect(ethicsCommitteeSpan).to.contain("The Ethics Committee Approval Statement was not found in the document. Since the research involves human beings or animals, it is necessary to state in the manuscript that the research has been approved by the Ethics Committee of the institution responsible for the research. Make sure to insert the statement in text format as an integral part of the manuscript. If the statement is in image format, please transcribe it in text format. Scanned images are not detected by the system.");
    });
}

function checkSubmissionCantbeFinished() {
    cy.get('#submitStep4Form > .formButtons > .submitFormButton').click();
    cy.get('.pkp_modal_panel > .footer > .pkpModalConfirmButton').click();
    cy.get('.notifyFormError > .description').should(cantFinishWithDocErrors => {
        expect(cantFinishWithDocErrors).to.contain("You can't finish the submission without fixing the errors of the submitted document");
    });
}

describe('Content Analysis Plugin - Error messages and submission blocking in step 4', function() {
    it("Check error messages shown in step 4 and whether submission can't be finished when document error occurs", function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();

        submissionStep1('en_US');
        submissionStep2();
        submissionStep3();
        checkErrorMesssagesInStep4();
        checkSubmissionCantbeFinished();
    });
    it("Check title message in step 4 when the english title is not informed in step 3", function() {
        cy.visit(Cypress.env('baseUrl') + 'index.php/scielo/submissions');
        loginAdminUser();

        cy.get('.pkpHeader__actions:visible > a.pkpButton').click();

        submissionStep1('pt_BR');
        submissionStep2();
        submissionStep3();

        cy.get('#statusTitleEnglish > .analysisStatusUnable').should('be.visible');
        cy.get('#statusTitleEnglish > span').should(titleSpan => {
            expect(titleSpan).to.contain("The checking for the english title in the PDF file couldn't be done, since this information wasn't filled in the submission's form");
        });
    });
});