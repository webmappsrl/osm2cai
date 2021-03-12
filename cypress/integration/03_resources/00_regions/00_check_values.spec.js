describe('Regions list', () => {
    beforeEach(() => {
        cy.visit('/nova/login');
        cy.get('input[name=email]').type('team@webmapp.it');
        cy.get('input[name=password]').type('webmapp');
        cy.contains('Login').click();
        cy.contains('Regioni').click();
        cy.wait(1000);
    });

    afterEach(() => {
        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.contains('Logout').click();
    });

    it('should show a table with name, code, provinces number, areas number, sectors number', () => {
        const tableSelector = 'table[data-testid=resource-table]',
            tableHeadSelector = tableSelector + ' > thead',
            tableBodySelector = tableSelector + ' > tbody';
        cy.get('h1').contains('Regioni').should('be.visible');
        cy.get(tableSelector).should('be.visible');
        cy.get(tableHeadSelector)
            .should('be.visible');
        cy.get(tableHeadSelector)
            .contains('name', {matchCase: false})
            .should('be.visible');
        cy.get(tableHeadSelector)
            .contains('code', {matchCase: false})
            .should('be.visible');
        cy.get(tableHeadSelector)
            .contains('provinces', {matchCase: false})
            .should('be.visible');
        cy.get(tableHeadSelector)
            .contains('areas', {matchCase: false})
            .should('be.visible');
        cy.get(tableHeadSelector)
            .contains('sectors', {matchCase: false})
            .should('be.visible');
        cy.get(tableBodySelector + ' > tr').each((tr) => {
            cy.wrap(tr).children('td').each((td) => {
                expect(td).to.be.visible;
                expect(td.text()).to.match(/.+/);
            });
        });
    });

    it('should show the download geojson/shape for all the rows', () => {
        const tableSelector = 'table[data-testid=resource-table]',
            tableBodySelector = tableSelector + ' > tbody';
        cy.get(tableBodySelector + ' > tr > td:last-child').each((td, index) => {
            cy.wrap(td)
                .contains('actions', {matchCase: false})
                .should('be.visible')
            cy.wrap(td).click();
            cy.contains('Download Geojson', {matchCase: false})
                .should('be.visible');
            cy.contains('Download Shape', {matchCase: false})
                .should('be.visible');
            cy.wrap(td).click();
        });
    });
})
