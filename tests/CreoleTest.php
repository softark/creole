<?php
/**
 * @copyright Copyright (c) 2014 Carsten Brandt / 2015 Nobuo Kihara
 * @license https://github.com/cebe/markdown/blob/master/LICENSE
 * @link https://github.com/cebe/markdown#readme
 */

namespace softark\creole\tests;

use softark\creole\Creole;

/**
 * Test case for the creole parser.
 *
 * @author Carsten Brandt <mail@cebe.cc>, Nobuo Kihara <softark@gmail.com>
 */
class CreoleTest extends \PHPUnit_Framework_TestCase
{
	protected $outputFileExtension = '.html';

	public function getDataPaths()
	{
		return [
			'creole-data' => __DIR__ . '/creole-data',
		];
	}

	/**
	 * @return Parser
	 */
	public function createCreole()
	{
		$creole = new Creole();
		$creole->wikiUrl = 'http://www.example.com/wiki/';
		$creole->externalWikis = [
			'Wiki-A' => 'http://www.wiki-a.com/wiki-a/',
			'Wiki-B' => 'https://www.wiki-b.com/wiki-b/',
		];

		return $creole;
	}

	/**
	 * @dataProvider dataFiles
	 */
	public function testParse($path, $file)
	{
		list($markdown, $html) = $this->getTestData($path, $file);
		// Different OS line endings should not affect test
		$html = str_replace(["\r\n", "\n\r", "\r"], "\n", $html);

		$m = $this->createCreole();
		$this->assertEquals($html, $m->parse($markdown));
	}

	/**
	 * @return Parser
	 */
	public function createCreoleEx()
	{
		$creole = new Creole();
		$creole->wikiUrl = 'http://www.example.com/wiki/';
		$creole->externalWikis = [
			'Wiki-A' => 'http://www.wiki-a.com/wiki-a/',
			'Wiki-B' => 'https://www.wiki-b.com/wiki-b/',
		];
		$creole->useRawHtml = true;
        $creole->rawHtmlFilter = function($input) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = \HTMLPurifier::getInstance($config);
            return $purifier->purify($input);
        };

		return $creole;
	}

	/**
	 * @dataProvider dataFilesEx
	 */
	public function testParseEx($path, $file)
	{
		list($markdown, $html) = $this->getTestDataEx($path, $file);
		// Different OS line endings should not affect test
		$html = str_replace(["\r\n", "\n\r", "\r"], "\n", $html);

		$m = $this->createCreoleEx();
		$this->assertEquals($html, $m->parse($markdown));
	}

	public function testUtf8()
	{
		$this->assertSame("<p>абвгдеёжзийклмнопрстуфхцчшщъыьэюя</p>\n", $this->createCreole()->parse('абвгдеёжзийклмнопрстуфхцчшщъыьэюя'));
		$this->assertSame("<p>there is a charater, 配</p>\n", $this->createCreole()->parse('there is a charater, 配'));
		$this->assertSame("<p>Arabic Latter \"م (M)\"</p>\n", $this->createCreole()->parse('Arabic Latter "م (M)"'));
		$this->assertSame("<p>電腦</p>\n", $this->createCreole()->parse('電腦'));

		$this->assertSame('абвгдеёжзийклмнопрстуфхцчшщъыьэюя', $this->createCreole()->parseParagraph('абвгдеёжзийклмнопрстуфхцчшщъыьэюя'));
		$this->assertSame('there is a charater, 配', $this->createCreole()->parseParagraph('there is a charater, 配'));
		$this->assertSame('Arabic Latter "م (M)"', $this->createCreole()->parseParagraph('Arabic Latter "م (M)"'));
		$this->assertSame('電腦', $this->createCreole()->parseParagraph('電腦'));
	}

//	public function testInvalidUtf8()
//	{
//		$m = $this->createCreole();
//		$this->assertEquals("<p><code>�</code></p>\n", $m->parse("`\x80`"));
//		$this->assertEquals('<code>�</code>', $m->parseParagraph("`\x80`"));
//	}

	public function pregData()
	{
		// http://en.wikipedia.org/wiki/Newline#Representations
		return [
			["a\r\nb", "a\nb"],
			["a\n\rb", "a\nb"], // Acorn BBC and RISC OS spooled text output :)
			["a\nb", "a\nb"],
			["a\rb", "a\nb"],

			["a\n\nb", "a\n\nb", "a</p>\n<p>b"],
			["a\r\rb", "a\n\nb", "a</p>\n<p>b"],
			["a\n\r\n\rb", "a\n\nb", "a</p>\n<p>b"], // Acorn BBC and RISC OS spooled text output :)
			["a\r\n\r\nb", "a\n\nb", "a</p>\n<p>b"],
		];
	}

	/**
	 * @dataProvider pregData
	 */
	public function testPregReplaceR($input, $exptected, $pexpect = null)
	{
		$this->assertSame($exptected, $this->createCreole()->parseParagraph($input));
		$this->assertSame($pexpect === null ? "<p>$exptected</p>\n" : "<p>$pexpect</p>\n", $this->createCreole()->parse($input));
	}

	public function getTestData($path, $file)
	{
		return [
			file_get_contents($this->getDataPaths()[$path] . '/' . $file . '.txt'),
			file_get_contents($this->getDataPaths()[$path] . '/' . $file . $this->outputFileExtension),
		];
	}

	public function getTestDataEx($path, $file)
	{
		return [
			file_get_contents($this->getDataPaths()[$path] . '/' . $file . '.txt'),
			file_get_contents($this->getDataPaths()[$path] . '/' . $file . '-ex' . $this->outputFileExtension),
		];
	}

	public function dataFiles()
	{
		$files = [];
		foreach ($this->getDataPaths() as $name => $src) {
			$handle = opendir($src);
			if ($handle === false) {
				throw new \Exception('Unable to open directory: ' . $src);
			}
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}

				if (substr($file, -4, 4) === '.txt' && file_exists($src . '/' . substr($file, 0, -4) .  $this->outputFileExtension)) {
					$files[] = [$name, substr($file, 0, -4)];
				}
			}
			closedir($handle);
		}
		return $files;
	}

	public function dataFilesEx()
	{
		$files = [];
		foreach ($this->getDataPaths() as $name => $src) {
			$handle = opendir($src);
			if ($handle === false) {
				throw new \Exception('Unable to open directory: ' . $src);
			}
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..') {
					continue;
				}

				if (substr($file, -4, 4) === '.txt' && file_exists($src . '/' . substr($file, 0, -4) . '-ex' . $this->outputFileExtension)) {
					$files[] = [$name, substr($file, 0, -4)];
				}
			}
			closedir($handle);
		}
		return $files;
	}
}
