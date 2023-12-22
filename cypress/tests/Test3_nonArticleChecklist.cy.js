import '../support/commands.js';

describe('Content Analysis Plugin - Standard checklist execution', function() {
    let submissionData;
    let files;
    
    before(function() {
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
});