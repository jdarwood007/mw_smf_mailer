<?php
/**
 * SMF Authentication
 *
 * @author Jeremy Darwood
 * @license BSD
 * @copyright: 2011 Jeremy Darwood
 *
 * @version 0.1
 */

// Setup our SMF Mailer.
class Mail
{
	// Sleek!
	function factory ($driver, $params = array())
	{
		$mailer = new Mail_SMF($params);
		return $mailer;
	}
}

// This is just a dummy class to prevent MediaWiki from complaining.
if (!class_exists('PEAR'))
{
	class PEAR
	{
		function isError($foo)
		{
			return false;
		}
	}
}

/*** SMF Mail! ***/
class Mail_SMF
{
	static $headers = array();

	// This will basically just sent it over to SMFs mailer.
	public function send($recipients, $headers, $body)
	{
		global $modSettings, $sourcedir, $wgSMFPath;

		self::$headers = $headers;

		// SMF may not be called yet.
		if (!defined('SMF') && !empty($wgSMFPath) && file_exists($wgSMFPath . '/SSI.php'))
			require_once($wgSMFPath . '/SSI.php');

		// First, integrate hook.
		$modSettings['integrate_outgoing_email'] = 'Mail_SMF::MailCallback';

		// Now we include the correct file and send the mail.
		// ! Note this means in SMF 2.0 that it may be in the mail queue.
		require_once($sourcedir . '/Subs-Post.php');
		sendmail($recipients, $headers['Subject'], $body);
	}

	// The callback is just to reset the headers.
	static function MailCallback($subject, $message, $headers)
	{
		global $wgPasswordSender, $wgSitename;
		global $wgSMFReturnPath;

		// Set a few options.
		$wiki_name = addcslashes($wgSitename, '<>()\'\\"');
		$line_break = "\r\n";

		// Now change our headers.
		$headers = 'From: ' . $wiki_name. ' <' . $wgPasswordSender . '>' . $line_break;
		$headers .= 'Return-Path: ' . (!empty($wgSMFReturnPath) ? $wgSMFReturnPath : $wgPasswordSender) . $line_break;
		$headers .= 'Date: ' . self::$headers['Date'] . $line_break;
		$headers .= 'X-Mailer: MediaWiki Mailer - SMF' . $line_break;
		$headers .= 'Message-ID: ' . self::$headers['Message-ID']. $line_break;
	}
}

?>