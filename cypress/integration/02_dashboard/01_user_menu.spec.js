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

    it('should exists', () => {
        cy.get('.v-popover.dropdown-right button.rounded')
            .should('be.visible');
    });

    it('should have the profile button', () => {
        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.get('#wm-user-profile-button').should('be.visible');
        cy.get('.v-popover.dropdown-right button.rounded').click();
    });

    it('should have the logout button', () => {
        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.get('#wm-user-logout-button').should('be.visible');
        cy.get('.v-popover.dropdown-right button.rounded').click();
    });
});
