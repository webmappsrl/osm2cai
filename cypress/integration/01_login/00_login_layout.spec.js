describe('Login page layout', () => {
    beforeEach(() => {
        cy.visit('/');
    })

    afterEach(() => {
    })

    it('should display the sosec logo', () => {
        cy.get('img[alt=SOSEC]').should('be.visible');
    })
})
