<?php
/**
 * @copyright Copyright (c) 2015,2023 Nobuo Kihara
 * @license https://github.com/softark/creole/blob/master/LICENSE
 * @link https://github.com/softark/creole#readme
 */

namespace softark\creole\block;

/**
 * Adds the table blocks
 */
trait TableTrait
{
    /**
     * identify a line as the beginning of a table block.
     */
    protected function identifyTable($line)
    {
        return strpos($line, '|') !== false && preg_match('/^\s*\|.*/', $line);
    }

    /**
     * Consume lines for a table
     */
    protected function consumeTable($lines, $current)
    {
        $pattern = <<< REGEXP
/(?<=\|)(
    ([^\|]*?{{{.*?}}}[^\|]*?)+|
    ([^\|]*~\|[^\|]*?)+|
    [^\|]*
)(?=\|)/x
REGEXP;
        // regexp pattern should be in the order of from specific/long/complicated to general/short/simple.

        $block = [
            'table',
            'rows' => [],
            'cols' => [],
        ];
        for ($i = $current, $count = count($lines); $i < $count; $i++) {
            $line = trim($lines[$i]);
            if ($line === '' || $line[0] !== '|') {
                break;
            }

            // extract alignment from second line
            if ($i == $current+1 && preg_match('~^\\s*\\|?(\\s*:?-[\\-\\s]*:?\\s*\\|?)*\\s*$~', $line)) {
                $cols = explode('|', trim($line, ' |'));
                foreach($cols as $col) {
                    $col = trim($col);
                    if (empty($col)) {
                        $block['cols'][] = '';
                        continue;
                    }
                    $l = ($col[0] === ':');
                    $r = (substr($col, -1, 1) === ':');
                    if ($l && $r) {
                        $block['cols'][] = 'center';
                    } elseif ($l) {
                        $block['cols'][] = 'left';
                    } elseif ($r) {
                        $block['cols'][] = 'right';
                    } else {
                        $block['cols'][] = '';
                    }
                }
                continue;
            }

            $header = $i === $current;
            preg_match_all($pattern, '|' . trim($line, '| ') . '|', $matches);
            $row = [];
            foreach ($matches[0] as $text) {
                $cell = [];
                if (isset($text[0]) && $text[0] === '=') {
                    $cell['tag'] = 'th';
                    $cell['text'] = $this->parseInline(trim(substr($text, 1)));
                } else {
                    $cell['tag'] = 'td';
                    $cell['text'] = $this->parseInline(trim($text));
                    $header = false;
                }
                $row['cells'][] = $cell;
            }
            $row['header'] = $header;
            $block['rows'][] = $row;
        }

        return [$block, --$i];
    }

    /**
     * render a table block
     */
    protected function renderTable($block)
    {
        $content = "";
        $first = true;
        $maxCols = count($block['cols']);
        foreach ($block['rows'] as $row) {
            if ($first) {
                if ($row['header']) {
                    $content .= "<thead>\n";
                } else {
                    $content .= "<tbody>\n";
                }
            }
            $content .= "<tr>\n";
            $numCols = 0;
            $tag = 'td';
            foreach ($row['cells'] as $n => $cell) {
                $tag = $cell['tag'];
                $class = '';
                if (!$first) {
                    $align = $block['cols'][$n];
                    if ($align != '') {
                        $class = " class=\"$align\"";
                    }
                }
                $cellText = $this->renderAbsy($cell['text']);
                if (empty($cellText)) {
                    $cellText = '&nbsp;';
                }
                $content .= "<$tag$class>$cellText</$tag>\n";
                $numCols++;
            }
            while ($numCols < $maxCols ) {
                $content .= "<$tag>&nbsp;</$tag>\n";
                $numCols++;
            }
            $content .= "</tr>\n";
            if ($first) {
                if ($row['header']) {
                    $content .= "</thead>\n<tbody>\n";
                }
                $first = false;
            }
        }
        return "<table>\n$colGroup\n$content</tbody>\n</table>\n";
    }

    abstract protected function parseInline($text);

    abstract protected function renderAbsy($absy);
}
