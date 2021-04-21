describe('Dashboard user menu', () => {
    beforeEach(() => {
        cy.visit('/');
        cy.get('input[name=email]').type('team@webmapp.it');
        cy.get('input[name=password]').type('webmapp');
        cy.get('button').contains('Login').click();
        cy.wait(1000);
    });

    afterEach(() => {
        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.get('#wm-user-logout-button').click();
    });

    it('should have sectors list', () => {
        cy.contains('my sectors', {matchCase: false})
            .should('be.visible');
    });
});
