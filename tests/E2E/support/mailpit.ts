import { APIRequestContext } from '@playwright/test';

const MAILPIT_API = 'http://localhost:8026/api/v1';

type Address = { Name: string; Address: string };

type Message = { From: Address; To: Address[]; Subject: string };

export async function emptyTheMailbox( request: APIRequestContext ): Promise< void > {
	await request.delete( `${ MAILPIT_API }/messages` );
}

export async function deliveredMail( request: APIRequestContext ) {
	const response = await request.get( `${ MAILPIT_API }/messages` );
	const { messages } = ( await response.json() ) as { messages: Message[] };

	return messages.map( ( message ) => ( {
		from: `${ message.From.Name } <${ message.From.Address }>`,
		to: message.To.map( ( recipient ) => recipient.Address ),
		subject: message.Subject,
	} ) );
}
