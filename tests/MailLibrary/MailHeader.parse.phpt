<?php
/** @var \greeny\MailLibrary\Connection $connection */
$connection = require __DIR__ . '/../bootstrap.php';

use Tester\Assert;

$header = new \greeny\MailLibrary\MailHeader([
	// example data that can be retrieved by calling imap_fetch_overview()
	'subject' => '=?utf-8?B?IFdlbGNvbWUgdG8geW91ciBuZXcgZW1haWwgYWNjb3VudC4=?=',
	'from' => '=?ISO-8859-1?Q?Keld_J=F8rn_Simonsen?= <postmaster@localhost.localdomain>',
	'to' => 'xyz@example.com',
	'date' => 'Fri,  6 Oct 2017 11:45:01 +0200 (CEST)',
	'message_id' => '<20171006094501.5F650A2F26@ispconfig.grifart.cz>',
	'size' => 1199,
	'uid' => 1,
	'msgno' => 1,
	'recent' => 0,
	'flagged' => 1,
	'answered' => 0,
	'deleted' => 0,
	'seen' => 1,
	'draft' => 0,
	'udate' => 1507283101,
]);

Assert::same('Welcome to your new email account.', $header->getSubject());
Assert::same('Keld Jørn Simonsen <postmaster@localhost.localdomain>', $header->getFrom());
Assert::same('xyz@example.com', $header->getTo());
Assert::same('2017-10-06T11:45:01+02:00', $header->getDateSent()->format('c'));
Assert::same('2017-10-06T09:45:01+00:00', $header->getDateReceived()->format('c'));

Assert::false($header->isRecent());
Assert::true($header->isFlagged());
Assert::false($header->isAnswered());
Assert::false($header->isDeleted());
Assert::true($header->isSeen());
Assert::false($header->isDraft());
