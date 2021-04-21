describe('Dashboard layout', () => {
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

    it('should have the regions count card', () => {
        cy.contains('total regions count', {matchCase: false})
            .should('be.visible');
    });

    it('should have the provinces count card', () => {
        cy.contains('total provinces count', {matchCase: false})
            .should('be.visible');
    });

    it('should have the areas count card', () => {
        cy.contains('total areas count', {matchCase: false})
            .should('be.visible');
    });

    it('should have the sectors count card', () => {
        cy.contains('total sectors count', {matchCase: false})
            .should('be.visible');
    });
});
