describe('Login page layout', () => {
    beforeEach(() => {
        cy.visit('/nova/login');
    })

    afterEach(() => {
    })

    it('should display the sosec logo', () => {
        cy.get('img[alt=SOSEC]').should('be.visible');
    })
})
