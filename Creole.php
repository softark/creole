<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole;

/**
 * Creole wiki parser for [Creole 1.0 spec](http://www.wikicreole.org/wiki/Creole1.0).
 *
 * @author Nobuo Kihara <softark@gmail.com>
 */
class Creole extends \cebe\markdown\Parser
{
// include block element parsing using traits
	use block\CodeTrait;
	use block\HeadlineTrait;
    use block\ListTrait;
    use block\TableTrait;
	use block\RuleTrait;

// include inline element parsing using traits
	use inline\CodeTrait;
	use inline\EmphStrongTrait;
	use inline\LinkTrait;

	/**
	 * @var boolean whether to format markup according to HTML5 spec.
	 * Defaults to `false` which means that markup is formatted as HTML4.
	 */
	public $html5 = false;

	/**
	 * Consume lines for a paragraph
	 *
	 * Allow block elements to break paragraphs
	 */
	protected function consumeParagraph($lines, $current)
	{
		// consume until newline
		$content = [];
		for ($i = $current, $count = count($lines); $i < $count; $i++) {
			$line = $lines[$i];
			if (!empty($line) && ltrim($line) !== '' &&
				!$this->identifyHeadline($line) &&
				!$this->identifyHr($line) &&
				!$this->identifyUl($line) &&
                !$this->identifyOl($line) &&
				!$this->identifyTable($line, $lines, $i) &&
				!$this->identifyCode($line)
			) {
				$content[] = $line;
			} else {
				break;
			}
		}
		$block = [
			'paragraph',
			'content' => $this->parseInline(implode("\n", $content)),
		];
		return [$block, --$i];
	}


	/**
	 * Parses escaped special characters.
	 * Creole uses tilde (~) for the escaping marker.
	 * It should escape the next character whatever it would be.
	 * @marker ~
	 */
	protected function parseEscape($text)
	{
		if (isset($text[1])) {
			return [['text', $text[1]], 2];
		}
		return [['text', $text[0]], 1];
	}

    /**
	 * @inheritdocs
	 *
	 * Parses a newline indicated by two backslashes, and
	 * escape '&', '<', and '>'.
	 */
	protected function renderText($text)
	{
		return str_replace(
			['&', '<', '>', "\\\\"],
			['&amp;', '&lt;', '&gt;', $this->html5 ? "<br>" : "<br />"],
			$text[1]);
	}
}
