describe('Content Analysis - Plugin setup', function () {
    it('Enables Content Analysis plugin', function () {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.contains('a', 'Website').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();

		cy.get('input[id^=select-cell-contentanalysisplugin]').check();
		cy.get('input[id^=select-cell-contentanalysisplugin]').should('be.checked');
    });
});