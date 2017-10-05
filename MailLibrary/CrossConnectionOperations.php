<?php

namespace greeny\MailLibrary;

/**
 * Provides operations with messages that need to be performed across messages
 * on different servers or connections.
 *
 * Please note, that this concept is more proof of concept and will be changed
 * in future in favor of methods directly at Mail object.
 */
final class CrossConnectionOperations
{

	public function copy(Mail $message, Mailbox $target)
	{
		$target->uploadRawMessage($message->getRawContent());
	}

	public function move(Mail $message, Mailbox $target)
	{
		$this->copy($message, $target);
		$message->delete();
	}

}
