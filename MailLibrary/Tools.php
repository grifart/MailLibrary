<?php declare(strict_types=1);

namespace greeny\MailLibrary;

use Nette\Utils\Strings;

final class Tools
{
	/**
	 * @param string $encodedText
	 * @return string valid, normalized UTF-8 string
	 */
	public static function decodeHeaderContent(string $encodedText): string
	{
		// for "=?ISO-8859-1?Q?Keld_J=F8rn_Simonsen?= <keld@example.com>"
		// returns
		// [
		//    {charset: 'ISO-8859-1', text: 'Keld Jørn Simonsen'},
		//    {charset: 'default', text: ' <keld@example.com>'},
		// ]
		$parts = imap_mime_header_decode($encodedText);

		$output = '';
		foreach($parts as $part) {
			assert(isset($part->charset));
			assert(isset($part->text));

			if($part->charset === 'UTF-8' || $part->charset === 'default') {
				$output .= $part->text;
				continue;
			}

			$output .= @mb_convert_encoding(
				$part->text,
				'UTF-8',
				$part->charset
			);
		}

		return Strings::trim(\Normalizer::normalize(Strings::normalize(Strings::fixEncoding($output))));
	}

}
