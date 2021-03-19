describe('Dashboard sidebar links', () => {
    beforeEach(() => {
        cy.visit('/');
        cy.get('input[name=email]').type('team@webmapp.it');
        cy.get('input[name=password]').type('webmapp');
        cy.get('button').contains('Login').click();
    });

    afterEach(() => {
        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.contains('Logout').click();
    });

    it('should exists', () => {
        cy.contains('Dashboard').should('be.visible');
    });

    it('should have the user list', () => {
        cy.contains('Utenti')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('include', 'users');
    });

    it('should have the territorial lists', () => {
        cy.contains('Territorio')
            .should('be.visible');
        cy.contains('Regioni')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('include', 'regions');
        cy.contains('Province')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('include', 'provinces');
        cy.contains('Aree')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('include', 'areas');
        cy.contains('Settori')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('include', 'sectors');
    });

    it('should have the link to the Webmapp Webapp', () => {
        cy.contains('Mappa')
            .should('be.visible')
            .and('have.attr', 'href')
            .and('include', 'osm2cai.j.webmapp.it');
    });
})
