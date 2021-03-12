describe('After seed', () => {
    describe('it should be able to login', () => {
        beforeEach(() => {
            cy.visit('/nova/login');
        });

        afterEach(() => {
            cy.get('.v-popover.dropdown-right button.rounded').click();
            cy.contains('Logout').click();
        });

        it('as Webmapp admin', () => {
            cy.get('input[name=email]').type('team@webmapp.it');
            cy.get('input[name=password]').type('webmapp');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Fabrizio Savini', () => {
            cy.get('input[name=email]').type('sosecguser@cai.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Alessio Piccioli', () => {
            cy.get('input[name=email]').type('alessiopiccioli@webmapp.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Andrea Del Sarto', () => {
            cy.get('input[name=email]').type('andreadel84@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Marco Barbieri', () => {
            cy.get('input[name=email]').type('marcobarbieri@webmapp.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Luca De Lucchi', () => {
            cy.get('input[name=email]').type('luca.delucchi@fmach.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Alessandro Geri', () => {
            cy.get('input[name=email]').type('aldogeri@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Alfredo Gattai', () => {
            cy.get('input[name=email]').type('alfredo.gattai@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Renato Boschi', () => {
            cy.get('input[name=email]').type('boschirenato@tiscali.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Enrico Sala', () => {
            cy.get('input[name=email]').type('enrico.sala@unimi.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Luca Grimaldi', () => {
            cy.get('input[name=email]').type('lucagri@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Luciano Turriani', () => {
            cy.get('input[name=email]').type('turluc47@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Giancarlo Tellini', () => {
            cy.get('input[name=email]').type('giancarlo.tellini@caitoscana.it');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Simone Bufalini', () => {
            cy.get('input[name=email]').type('bufalini.simone@cemes-spa.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Aldo Mancini', () => {
            cy.get('input[name=email]').type('aldo2346@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Gianbattista Condorelli', () => {
            cy.get('input[name=email]').type('giambattista.condorelli@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Vincenzo Agliata', () => {
            cy.get('input[name=email]').type('v.agliata@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Danilo Baggini', () => {
            cy.get('input[name=email]').type('danilo.baggini@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });

        it('as Carlo Prosperi', () => {
            cy.get('input[name=email]').type('carlopr54@gmail.com');
            cy.get('input[name=password]').type('osm2cai');
            cy.get('button').contains('Login').click();
            cy.url().should('contain', 'dashboards');
        });
    })
})
