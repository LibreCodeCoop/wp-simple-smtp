import { expect, Page, test } from '@playwright/test';

import { logInAsTheAdmin } from './support/admin';
import { deliveredMail, emptyTheMailbox } from './support/mailpit';

async function sendTheTestEmail( page: Page ): Promise< void > {
	await page.getByLabel( 'Recipient Email Address' ).fill( 'admin@example.org' );
	await page.getByRole( 'button', { name: 'Send Test Email' } ).click();
}

test.describe( 'Sending WordPress mail through SMTP', () => {
	test.beforeEach( async ( { request } ) => {
		await emptyTheMailbox( request );
	} );

	test( 'delivers the mail WordPress sends with the configured sender', async ( { page, request } ) => {
		await page.goto( '/wp-login.php?action=lostpassword' );
		await page.getByLabel( 'Username or Email Address' ).fill( 'admin' );
		await page.getByRole( 'button', { name: 'Get New Password' } ).click();

		await expect.poll( () => deliveredMail( request ) ).toEqual( [
			{ from: 'Simple SMTP <noreply@example.org>', to: [ 'admin@example.org' ], subject: '[E2E] Password Reset' },
		] );
	} );

	test( 'sends the test email from the settings page', async ( { page, request } ) => {
		await logInAsTheAdmin( page );
		await page.goto( '/wp-admin/options-general.php?page=wpss-settings' );
		await sendTheTestEmail( page );

		await expect( page.getByText( 'Email sent successfully to admin@example.org.' ) ).toBeVisible();
		await expect.poll( () => deliveredMail( request ) ).toEqual( [
			{ from: 'Simple SMTP <noreply@example.org>', to: [ 'admin@example.org' ], subject: 'Test Email' },
		] );
	} );

	test( 'keeps a password with a quote working after saving the settings twice', async ( { page, request } ) => {
		await logInAsTheAdmin( page );
		await page.goto( '/wp-admin/options-general.php?page=wpss-settings' );
		await page.getByRole( 'button', { name: 'Save settings' } ).click();
		await page.getByRole( 'button', { name: 'Save settings' } ).click();

		await expect( page.getByLabel( 'smtp_pass' ) ).toHaveValue( "pa'ss" );
		await sendTheTestEmail( page );
		await expect( page.getByText( 'Email sent successfully to admin@example.org.' ) ).toBeVisible();
		await expect.poll( () => deliveredMail( request ) ).toHaveLength( 1 );
	} );
} );
