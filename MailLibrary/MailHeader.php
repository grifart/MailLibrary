<?php declare(strict_types=1);

namespace greeny\MailLibrary;

use Nette\Utils\DateTime;

/**
 * Strict version of message headers from imap_fetch_overview()
 *
 * @link http://php.net/manual/en/function.imap-fetch-overview.php
 */
final class MailHeader
{
	/**
	 * the messages subject
	 * @var string
	 */
	private $subject;

	/**
	 * who sent it
	 * @var string
	 */
	private $from;

	/**
	 * recipient
	 * @var string
	 */
	private $to;

	/**
	 * when was it sent
	 * @var \DateTimeImmutable
	 */
	private $dateSent;

	/**
	 * Arrival date
	 * @var ?\Nette\Utils\DateTime
	 */
	private $dateReceived;

	/**
	 * Message-ID
	 * @var string
	 */
	private $messageId;

	/**
	 * is a reference to this message id
	 * @var ?string
	 */
	private $references;

	/**
	 * is a reply to this message id
	 * @var ?string
	 */
	private $inReplyTo;

	/**
	 * size in bytes
	 * @var int
	 */
	private $sizeInBytes;

	/**
	 * UID the message has in the mailbox
	 * @var int
	 */
	private $uid;

	/**
	 * message sequence number in the mailbox
	 * @var int
	 */
	private $messageNumber;

	/**
	 * this message is flagged as recent
	 * @var bool
	 */
	private $recent;

	/**
	 * this message is flagged
	 * @var bool
	 */
	private $flagged;

	/**
	 * this message is flagged as answered
	 * @var bool
	 */
	private $answered;

	/**
	 * this message is flagged for deletion
	 * @var bool
	 */
	private $deleted;

	/**
	 * this message is flagged as already read
	 * @var bool
	 */
	private $seen;

	/**
	 * this message is flagged as being a draft
	 * @var bool
	 */
	private $draft;



	public function __construct(array $fetchedData)
	{
		assert(\is_string($fetchedData['subject']));
		$this->subject = Tools::decodeHeaderContent($fetchedData['subject']);

		assert(\is_string($fetchedData['from']));
		$this->from = Tools::decodeHeaderContent($fetchedData['from']);

		assert(\is_string($fetchedData['to']));
		$this->to = Tools::decodeHeaderContent($fetchedData['to']);

		// @link https://tools.ietf.org/html/rfc2156#section-3.3.5
		// However parsing as ISO822 does not always work; so falling back to auto-detection
		assert(\is_string($fetchedData['date']));
		$this->dateSent = new \DateTimeImmutable($fetchedData['date']);

		$this->dateReceived = isset($fetchedData['udate'])
			? \DateTimeImmutable::createFromFormat('U', (string) $fetchedData['udate'])
			: NULL;

		$this->messageId = $fetchedData['message_id'] ?? NULL;
		$this->references = $fetchedData['references'] ?? NULL;
		$this->inReplyTo = $fetchedData['in_reply_to'] ?? NULL;
		
		assert(\is_int($fetchedData['size']));
		$this->sizeInBytes = $fetchedData['size'];

		assert(\is_int($fetchedData['uid']));
		$this->uid = $fetchedData['uid'];
		
		assert(\is_int($fetchedData['msgno']));
		$this->messageNumber = $fetchedData['msgno'];
		
		assert(\in_array($fetchedData['recent'], [0,1], TRUE));
		$this->recent = (bool) $fetchedData['recent'];

		assert(\in_array($fetchedData['flagged'], [0,1], TRUE));
		$this->flagged = (bool) $fetchedData['flagged'];

		assert(\in_array($fetchedData['answered'], [0,1], TRUE));
		$this->answered = (bool) $fetchedData['answered'];

		assert(\in_array($fetchedData['deleted'], [0,1], TRUE));
		$this->deleted = (bool) $fetchedData['deleted'];

		assert(\in_array($fetchedData['seen'], [0,1], TRUE));
		$this->seen = (bool) $fetchedData['seen'];

		assert(\in_array($fetchedData['draft'], [0,1], TRUE));
		$this->draft = (bool) $fetchedData['draft'];
	}


	public function getSubject(): string
	{
		return $this->subject;
	}


	public function getFrom(): string
	{
		return $this->from;
	}


	public function getTo(): string
	{
		return $this->to;
	}


	public function getDateSent(): \DateTimeImmutable
	{
		return $this->dateSent;
	}


	public function getDateReceived(): ?\DateTimeImmutable
	{
		return $this->dateReceived;
	}


	public function getMessageId(): string
	{
		return $this->messageId;
	}


	public function getReferences(): ?string
	{
		return $this->references;
	}


	public function getInReplyTo(): ?string
	{
		return $this->inReplyTo;
	}


	public function getSizeInBytes(): int
	{
		return $this->sizeInBytes;
	}


	public function getUid(): int
	{
		return $this->uid;
	}


	public function getMessageNumber(): int
	{
		return $this->messageNumber;
	}


	public function isRecent(): bool
	{
		return $this->recent;
	}


	public function isFlagged(): bool
	{
		return $this->flagged;
	}


	public function isAnswered(): bool
	{
		return $this->answered;
	}


	public function isDeleted(): bool
	{
		return $this->deleted;
	}


	public function isSeen(): bool
	{
		return $this->seen;
	}


	public function isDraft(): bool
	{
		return $this->draft;
	}



}
