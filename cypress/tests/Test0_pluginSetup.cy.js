import '../support/commands.js';

describe('Content Analysis - Plugin setup', function () {
    it('Enables Content Analysis plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('nav').contains('Settings').click();
		cy.get('nav').contains('Website').click({ force: true });

		cy.waitJQuery();
		cy.get('button[id="plugins-button"]').click();

		cy.get('input[id^=select-cell-contentanalysisplugin]').check();
		cy.get('input[id^=select-cell-contentanalysisplugin]').should('be.checked');
    });
});
