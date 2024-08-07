describe('Users admin', () => {
    beforeEach(() => {
        cy.visit('/');
        cy.get('input[name=email]').type('team@webmapp.it');
        cy.get('input[name=password]').type('webmapp');
        cy.contains('Login').click();
        cy.contains('Utenti').click();
        cy.wait(1000);
    });

    afterEach(() => {
        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.contains('Logout').click();
    });

    it('should show a table with name, email, is admin, is national referent, region and provinces', () => {
        const tableSelector = 'table[data-testid=resource-table]',
            tableHeadSelector = tableSelector + ' > thead',
            tableBodySelector = tableSelector + ' > tbody';
        cy.get('h1').contains('Utenti').should('be.visible');
        cy.get(tableSelector).should('be.visible');
        cy.get(tableHeadSelector)
            .should('be.visible');

        let labels = ['name', 'email', 'admin', 'national referent', 'region', 'provinces'];
        for (let label of labels) {
            cy.get(tableHeadSelector)
                .contains(label, {matchCase: false})
                .should('be.visible');
        }

        cy.get(tableBodySelector + ' > tr').each((tr) => {
            cy.wrap(tr).children('td').each((td) => {
                expect(td).to.be.visible;
            });
        });
    });

    let user = {
        name: "username",
        email: "useremail@webmapp.it",
        password: "osm2cai-osm2cai",
        region: "Toscana"
    };

    it('should be able to create a user', () => {
        // cy.contains('td', user.name).should('not.exist');
        cy.contains('create utenti', {matchCase: false})
            .should('be.visible')
            .click();
        cy.wait(1000);

        let labels = ['name', 'email', 'password', 'admin', 'national referent', 'region'];
        for (let label of labels) {
            cy.contains('label', label, {matchCase: false})
                .should('be.visible');
        }

        cy.get('#name')
            .should('be.visible')
            .type(user.name);
        cy.get('#email')
            .should('be.visible')
            .type(user.email);
        cy.get('#password')
            .should('be.visible')
            .type(user.password);
        cy.get('select[data-testid=regions-select]')
            .should('be.visible')
            .select(user.region);
        cy.contains('a', 'cancel', {matchCase: false})
            .should('be.visible');
        cy.contains('button', 'create utenti', {matchCase: false})
            .should('be.visible')
            .click();

        cy.contains('ul > li > a', 'Utenti').click();

        cy.contains('td', user.name)
            .should('be.visible');
        cy.contains('td', user.email)
            .should('be.visible');

        cy.get('.v-popover.dropdown-right button.rounded').click();
        cy.contains('Logout').click();

        cy.visit('/');
        cy.get('input[name=email]').type(user.email);
        cy.get('input[name=password]').type(user.password);
        cy.contains('Login').click();
        cy.url().should('contain', 'dashboard');
    });

    describe('associations update', () => {
        beforeEach(() => {
            let tr = cy.contains('td', user.name)
                .should('exist')
                .parent('tr');
            tr.invoke('attr', 'dusk').then((dusk) => {
                let id = dusk.split('-')[0];
                cy.get('[dusk=' + id + '-view-button]')
                    .click();
                cy.wait(1000);
            });
        });

        describe('should be able to add', () => {
            it('a sector', () => {
                cy.contains('a', 'attach settori', {matchCase: false})
                    .click();
                cy.wait(1000);
                cy.get('select > option:nth-child(3)').then((select) => {
                    let optionValue = select.val(),
                        optionText = select.text().replace('\\n', '').trim();
                    cy.contains('select', 'choose', {matchCase: false})
                        .should('exist')
                        .and('be.visible')
                        .select(optionValue);
                    cy.contains('button', 'attach settori', {matchCase: false})
                        .click();
                    cy.wait(1000);
                    cy.contains('h4', 'sectors', {matchCase: false}).parent().parent().find('p')
                        .should('contain.text', optionText);
                });
            });

            it('an area', () => {
                cy.contains('a', 'attach aree', {matchCase: false})
                    .click();
                cy.wait(1000);
                cy.get('select > option:nth-child(3)').then((select) => {
                    let optionValue = select.val(),
                        optionText = select.text().replace('\\n', '').trim();
                    cy.contains('select', 'choose', {matchCase: false})
                        .should('exist')
                        .and('be.visible')
                        .select(optionValue);
                    cy.contains('button', 'attach aree', {matchCase: false})
                        .click();
                    cy.wait(1000);
                    cy.contains('h4', 'areas', {matchCase: false}).parent().parent().find('p')
                        .should('contain.text', optionText);
                });
            });

            it('a province', () => {
                cy.contains('a', 'attach province', {matchCase: false})
                    .click();
                cy.wait(1000);
                cy.get('select > option:nth-child(3)').then((select) => {
                    let optionValue = select.val(),
                        optionText = select.text().replace('\\n', '').trim();
                    cy.contains('select', 'choose', {matchCase: false})
                        .should('exist')
                        .and('be.visible')
                        .select(optionValue);
                    cy.contains('button', 'attach province', {matchCase: false})
                        .click();
                    cy.wait(1000);
                    cy.contains('h4', 'provinces', {matchCase: false}).parent().parent().find('p')
                        .should('contain.text', optionText);
                });
            });
        });

        describe('should be able to remove', () => {
            it('a sector', () => {
                cy.get('button[data-testid=sectors-items-0-delete-button]')
                    .click();
                cy.contains('button', 'detach', {matchCase: false})
                    .click();
            });

            it('an area', () => {
                cy.get('button[data-testid=areas-items-0-delete-button]')
                    .click();
                cy.contains('button', 'detach', {matchCase: false})
                    .click();
            });

            it('a province', () => {
                cy.get('button[data-testid=provinces-items-0-delete-button]')
                    .click();
                cy.contains('button', 'detach', {matchCase: false})
                    .click();
            });
        });
    });

    describe('and finally', () => {
        it('should be able to delete a user', () => {
            let tr = cy.contains('td', user.name).parent('tr');
            tr.invoke('attr', 'dusk').then((dusk) => {
                let id = dusk.split('-')[0];
                cy.get('[dusk=' + id + '-delete-button]')
                    .click();
                cy.get('button#confirm-delete-button')
                    .click();
            });
        });
    });

    it('should be able to emulate a user', () => {
        let emulateButton = cy.get('tr[dusk=7-row]').contains('emulate', {matchCase: false});
        emulateButton.should('be.visible');
        emulateButton.click();
        cy.wait(1000);
        let user = cy.get('.v-popover.dropdown-right button.rounded').contains('Alessandro Geri');
        user.should('be.visible');
        user.click();
        let restore = cy.contains('restore user', {matchCase: false});
        restore.should('be.visible');
        restore.click();
        user = cy.get('.v-popover.dropdown-right button.rounded').contains('Webmapp Team');
        user.should('be.visible');
    });
});
