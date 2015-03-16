<?php
/**
 * @copyright Copyright (c) 2015 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\inline;

// work around https://github.com/facebook/hhvm/issues/1120
defined('ENT_HTML401') || define('ENT_HTML401', 0);

/**
 * Adds links and images as well as url markers.
 *
 */
trait LinkTrait
{
    /**
     * @var string the base url of the wiki which is used for the internal links.
     */
    public $wikiUrl = '';

    /**
     * @var array a list of external wikis referenced in this document.
     * An element in the list should be a key-value pair in which key is the wiki name
     * and the value is the url of it.
     */
    public $externalWikis = [];

    /**
     * @var string regular expression pattern to detect an url
     * This does not allow '[' and ']' in the url.
     */
    private $_urlRegexp = <<< REGEXP
/(?(R) # in case of recursion match parentheses
\(((?>[^\s()]+)|(?R))*\)
|      # else match a link
^(https?|ftp):\/\/(([^\s\[\]\<\>()]+)|(?R))+(?<![\[\]\<\>\.,:;\'"!\?\s])
)/x
REGEXP;

    /**
     * Parses urls and adds auto linking feature.
     * @marker http
     * @marker ftp
     */
    protected function parseUrl($markdown)
    {
        if (!in_array('parseLink', $this->context) && preg_match($this->_urlRegexp, $markdown, $matches)) {
            return [
                ['autoUrl', $matches[0]],
                strlen($matches[0])
            ];
        }
        return [['text', substr($markdown, 0, 6)], 6];
    }

    protected function renderAutoUrl($block)
    {
        $href = htmlspecialchars($block[1], ENT_COMPAT | ENT_HTML401, 'UTF-8');
        $text = htmlspecialchars(urldecode($block[1]), ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return "<a href=\"$href\">$text</a>";
    }

    /**
     * Parses urls starting with a tilde (~) into a plain text.
     * @marker ~http
     * @marker ~ftp
     */
    protected function parseEscapedUrl($markdown)
    {
        $text = substr($markdown, 1);
        if (preg_match($this->_urlRegexp, $text, $matches)) {
            return [
                ['text', $matches[0]],
                strlen($matches[0]) + 1
            ];
        }
        return [['text', substr($markdown, 0, 4)], 4];
    }

    /**
     * Parses a link: [[ ... ]]
     * @marker [[
     */
    protected function parseLink($markdown)
    {
        if (!in_array('parseLink', array_slice($this->context, 1)) && preg_match('/^\[\[(.*?)\]\]/', $markdown, $matches)) {
            // '[[link]]
            if (preg_match('/^(.+?)\|(.*)$/', $matches[1], $parts)) {
                // 'link|text' pattern
                $url = $parts[1];
                $text = ($parts[2] == '') ? $parts[1] : $parts[2];
            } else {
                // 'link' only pattern
                $url = $matches[1];
                $text = $matches[1];
            }
            if (!preg_match('/^(https?|ftp):\/\//', $url)) {
                // not an external link, i.e., a wiki link
                if (preg_match('/^(.*):(.*)$/', $url, $urlMatches)) {
                    // inter wiki link
                    $extWiki = $urlMatches[1];
                    if (isset($this->externalWikis[$extWiki])) {
                        $url = $this->externalWikis[$extWiki] . urlencode($urlMatches[2]);
                    } else {
                        return [
                            [
                                'text',
                                $matches[0],
                            ],
                            strlen($matches[0])
                        ];
                    }
                } else {
                    // internal link
                    $url = $this->wikiUrl . urlencode($url);
                }
            }
            return [
                [
                    'link',
                    'text' => $this->parseInline($text),
                    'url' => $url,
                ],
                strlen($matches[0])
            ];
        } else {
            // remove all starting [ markers to avoid next one to be parsed as link
            $result = '[[';
            $i = 2;
            while (isset($markdown[$i]) && $markdown[$i] == '[') {
                $result .= '[';
                $i++;
            }
            return [['text', $result], $i];
        }
    }

    /**
     * Parses an image: {{ ... }}
     * @marker {{
     */
    protected function parseImage($markdown)
    {
        if (preg_match('/^\{\{(.*?)\}\}/', $markdown, $matches)) {
            // '{{image}}
            if (preg_match('/^(.+?)\|(.*)$/', $matches[1], $parts)) {
                // 'image|text' pattern
                $url = $parts[1];
                $text = ($parts[2] == '') ? $parts[1] : $parts[2];
            } else {
                // 'image' only pattern
                $url = $matches[1];
                $text = $matches[1];
            }
            if ($url == $text) {
                if (preg_match('/([^\/]+\.(jpe?g|png|gif))/i', $url, $fileMatches)) {
                    $text = $fileMatches[1];
                }
            }
            return [
                [
                    'image',
                    'text' => $text,
                    'url' => $url,
                ],
                strlen($matches[0])
            ];
        } else {
            // remove all starting [ markers to avoid next one to be parsed as link
            $result = '{{';
            $i = 2;
            while (isset($markdown[$i]) && $markdown[$i] == '{') {
                $result .= '[';
                $i++;
            }
            return [['text', $result], $i];
        }
    }

    protected function renderUrl($block)
    {
        $url = htmlspecialchars($block[1], ENT_COMPAT | ENT_HTML401, 'UTF-8');
        $text = htmlspecialchars(urldecode($block[1]), ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return "<a href=\"$url\">$text</a>";
    }

    protected function renderLink($block)
    {
        return '<a href="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
        . '>' . $this->renderAbsy($block['text']) . '</a>';
    }

    protected function renderImage($block)
    {
        return '<img src="' . htmlspecialchars($block['url'], ENT_COMPAT | ENT_HTML401, 'UTF-8') . '"'
        . ' alt="' . htmlspecialchars($block['text'], ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8') . '"'
        . ($this->html5 ? '>' : ' />');
    }

    abstract protected function parseInline($text);
    abstract protected function renderAbsy($absy);
}
