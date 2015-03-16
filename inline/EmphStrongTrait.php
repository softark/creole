<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\inline;

/**
 * Adds inline emphasizes and strong elements
 */
trait EmphStrongTrait
{
	/**
	 * Parses strong element: ** ... **
	 * @marker **
	 */
	protected function parseStrong($text)
	{
		// must take care of code element(s)
		$pattern =<<< REGEXP
/^\*\*(
(.*{{{.*?}}}.*)+?|
.+?
)\*\*/sx
REGEXP;

		if (preg_match($pattern, $text, $matches)) {
			return [
				[
					'strong',
					$this->parseInline($matches[1])
				],
				strlen($matches[0])
			];
		}
		return [['text', '**'], 2];
	}

	/**
	 * Parses strong element: // ... //
	 * @marker //
	 */
	protected function parseEmph($text)
	{
		// must take care of code elements, 'http://', 'https://', and 'ftp://'
		$pattern =<<< REGEXP
/^\/\/(
(.*{{{.*?}}}.*)+?|
.+?
)(?<!http:|(?<=h)ttps:|(?<=f)tp:)\/\/(?!\/)/sx
REGEXP;
		if (preg_match($pattern, $text, $matches)) {
			return [
				[
					'emph',
					$this->parseInline($matches[1])
				],
				strlen($matches[0])
			];
		}
		return [['text', '//'], 2];
	}

	protected function renderStrong($block)
	{
		return '<strong>' . $this->renderAbsy($block[1]) . '</strong>';
	}

	protected function renderEmph($block)
	{
		return '<em>' . $this->renderAbsy($block[1]) . '</em>';
	}

	abstract protected function parseInline($text);
	abstract protected function renderAbsy($absy);
}
