<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use greeny\MailLibrary\Structures\IStructure;
use Nette\Utils\Strings;

class Mail {
	const ANSWERED = 'ANSWERED';
	const BCC = 'BCC';
	const BEFORE = 'BEFORE';
	const BODY = 'BODY';
	const CC = 'CC';
	const DELETED = 'DELETED';
	const FLAGGED = 'FLAGGED';
	const FROM = 'FROM';
	const KEYWORD = 'KEYWORD';
	const NEW_MESSAGES = 'NEW';
	const NOT_KEYWORD = 'UNKEYWORD';
	const OLD_MESSAGES = 'OLD';
	const ON = 'ON';
	const RECENT = 'RECENT';
	const SEEN = 'SEEN';
	const SINCE = 'SINCE';
	const SUBJECT = 'SUBJECT';
	const TEXT = 'TEXT';
	const TO = 'TO';

	const FLAG_ANSWERED = "\\ANSWERED";
	const FLAG_DELETED = "\\DELETED";
	const FLAG_DRAFT = "\\DRAFT";
	const FLAG_FLAGGED = "\\FLAGGED";
	const FLAG_SEEN = "\\SEEN";

	const ORDER_DATE = SORTARRIVAL;
	const ORDER_FROM = SORTFROM;
	const ORDER_SUBJECT = SORTSUBJECT;
	const ORDER_TO = SORTTO;
	const ORDER_CC = SORTCC;
	const ORDER_SIZE = SORTSIZE;

	/** @var Connection */
	protected $connection;

	/** @var Mailbox */
	protected $mailbox;

	/** @var int */
	protected $id;

	/** @var array */
	protected $headers = NULL;

	/** @var IStructure */
	protected $structure = NULL;

	/** @var array */
	protected $flags = NULL;

	/**
	 * @param Connection $connection
	 * @param Mailbox    $mailbox
	 * @param int        $id
	 */
	public function __construct(Connection $connection, Mailbox $mailbox, $id)
	{
		$this->connection = $connection;
		$this->mailbox = $mailbox;
		$this->id = $id;
	}

	/**
	 * Header checker
	 *
	 * @param $name
	 * @return bool
	 */
	public function __isset($name)
	{
		$this->headers !== NULL || $this->initializeHeaders();
		$key = $this->normalizeHeaderName($this->lowerCamelCaseToHeaderName($name));
		return isset($this->headers[$key]);
	}

	/**
	 * Header getter
	 *
	 * @param string $name
	 * @return mixed
	 * @deprecated
	 */
	public function __get($name)
	{
		\trigger_error(\E_USER_DEPRECATED, 'use array access with execat header name instead');
		return $this->getHeader(
			$this->normalizeHeaderName($this->lowerCamelCaseToHeaderName($name))
		);
	}

	public function __set($name, $value) {
		throw new \Exception('Mail headers are read-only.');
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return Mailbox
	 */
	public function getMailbox()
	{
		return $this->mailbox;
	}

	/**
	 * @return string[]
	 */
	public function getHeaders()
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return $this->headers;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getHeader($name)
	{
		$this->headers !== NULL || $this->initializeHeaders();
		$index = $this->normalizeHeaderName($name);
		if(isset($this->headers[$index])) {
			return $this->headers[$index];
		}

		return NULL;
	}

	/**
	 * @return Contact|null
	 */
	public function getSender() {
		$from = $this->getHeader('from');
		if($from) {
			$contacts = $from->getContactsObjects();
			return (count($contacts) ? $contacts[0] : NULL);
		}

		return NULL;
	}

	/**
	 * @return string
	 */
	public function getBody()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getBody();
	}

	/**
	 * @return string
	 */
	public function getHtmlBody()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getHtmlBody();
	}

	/**
	 * @return string
	 */
	public function getTextBody()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getTextBody();
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getAttachments();
	}

	/**
	 * @return \greeny\MailLibrary\MimePart[]
	 */
	public function getMimeParts()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getMimeParts();
	}

	/**
	 * @return array
	 */
	public function getFlags()
	{
		$this->flags !== NULL || $this->initializeFlags();
		return $this->flags;
	}

	public function setFlags(array $flags, $autoFlush = FALSE)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		foreach(array(
			Mail::FLAG_ANSWERED,
			Mail::FLAG_DELETED,
			Mail::FLAG_DELETED,
			Mail::FLAG_FLAGGED,
			Mail::FLAG_SEEN,
		) as $flag) {
			if(isset($flags[$flag])) {
				$this->connection->getDriver()->setFlag($this->id, $flag, $flags[$flag]);
			}
		}
		if($autoFlush) {
			$this->connection->getDriver()->flush();
		}
	}

	public function move($toMailbox)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->moveMail($this->id, $toMailbox);
	}

	public function copy($toMailbox)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->copyMail($this->id, $toMailbox);
	}

	public function delete()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->deleteMail($this->id);
	}

	/** @deprecated */
	public function saveToEml($file)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		if(\file_put_contents($file, $this->connection->getDriver()->retrieveRawMessage($this->id)) === FALSE) {
			throw new MailException('Cannot save you e-mail to disk. I/O error occurred');
		}
	}

	/**
	 * Returns raw message content. This can be saved as eml file.
	 *
	 * @see \greeny\MailLibrary\Mailbox::uploadRawMessage() is inverse method
	 *
	 * @return string the raw message content
	 * @throws \greeny\MailLibrary\DriverException
	 */
	public function getRawContent()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		return $this->connection->getDriver()->retrieveRawMessage($this->id);
	}

	public function getHeaderInfo()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		return $this->connection->getDriver()->getHeaderInfo($this->id);
	}

	/**
	 * Initializes headers
	 */
	protected function initializeHeaders()
	{
		$this->headers = array();
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		foreach($this->connection->getDriver()->getHeaders($this->id) as $key => $value) {
			$this->headers[$this->normalizeHeaderName($key)] = $value;
		}
	}

	protected function initializeStructure()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->structure = $this->connection->getDriver()->getStructure($this->id, $this->mailbox);
	}

	/**
	 * @internal
	 * @return IStructure
	 */
	public function getStructure() {
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure;
	}

	protected function initializeFlags()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->flags = $this->connection->getDriver()->getFlags($this->id);
	}

	/**
	 * Formats header name (X-Received-From => x-recieved-from)
	 *
	 * @param string $name Header name (with dashes, valid UTF-8 string)
	 * @return string
	 */
	protected function normalizeHeaderName($name)
	{
		return Strings::normalize(Strings::lower($name));
	}
	
	/**
	 * Converts camel cased name to normalized header name (xReceivedFrom => x-recieved-from)
	 *
	 * @param string $camelCasedName
	 * @return string name with dashes
	 */
	protected function lowerCamelCaseToHeaderName($camelCasedName) {
		// todo: test this
		// todo: use something like this instead http://stackoverflow.com/a/1993772
		$dashedName = lcfirst(preg_replace_callback("~-.~", function($matches){
			return ucfirst(substr($matches[0], 1));
		}, $camelCasedName));
		
		return $this->normalizeHeaderName($dashedName);
	}
}
