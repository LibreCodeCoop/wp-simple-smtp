import { defineConfig, devices } from '@playwright/test';

export default defineConfig( {
	testDir: 'tests/E2E',
	outputDir: 'tests/E2E/.results',
	workers: 1,
	fullyParallel: false,
	forbidOnly: Boolean( process.env.CI ),
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [
				[ 'github' ],
				[
					'html',
					{ open: 'never', outputFolder: 'tests/E2E/.report' },
				],
		  ]
		: [ [ 'list' ] ],
	use: {
		...devices[ 'Desktop Chrome' ],
		baseURL: 'http://localhost:8889',
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
} );
