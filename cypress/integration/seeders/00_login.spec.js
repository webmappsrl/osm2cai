describe('After seed', () => {
    describe('it should be able to login', () => {
        beforeEach(() => {
            cy.visit('/nova/login');
        })

        afterEach(() => {
            cy.get('.v-popover.dropdown-right button.rounded').click();
            cy.contains('Logout').click();
        })

        it('as Webmapp admin', () => {
            cy.get('input[name=email]').type('team@webmapp.it');
            cy.get('input[name=password]').type('webmapp');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        })

        it('as Alessio Piccioli', () => {
            cy.get('input[name=email]').type('alessiopiccioli@webmapp.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        })
    })
})
