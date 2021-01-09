creole parser extended from cebe/markdown
=========================================

[![Build Status](https://travis-ci.org/softark/creole.svg?branch=master)](https://travis-ci.org/softark/creole)
[![Code Coverage](https://scrutinizer-ci.com/g/softark/creole/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/softark/creole/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/softark/creole/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/softark/creole/?branch=master)

What is this? <a name="what"></a>
-------------

This is a [Creole Wiki](http://www.wikicreole.org/wiki/Creole1.0) parser for PHP built upon [cebe/markdown parser for PHP](https://github.com/cebe/markdown).

Installation <a name="installation"></a>
------------

[PHP 5.4 or higher](http://www.php.net/downloads.php) is required to use it.
It will also run on facebook's [hhvm](http://hhvm.com/).

Installation is recommended to be done via [composer][] by running:

	composer require softark/creole "~1.2"

Alternatively you can add the following to the `require` section in your `composer.json` manually:

```json
"softark/creole": "~1.2"
```

Run `composer update` afterwards.

Note that the installation automatically includes the dependent packages, especially "cebe/markdown", to make the creole parser functional.

[composer]: https://getcomposer.org/ "The PHP package manager"


Usage <a name="usage"></a>
-----

The usage of the creole parser is similar to that of cebe/markdown parser.

### In your PHP project

To parse your wiki text you need only two lines of code. The first one is to create the creole parser instance:

```
$parser = new \softark\creole\Creole();
```

The next step is to call the `parse()`-method for parsing the text using the full wiki language
or calling the `parseParagraph()`-method to parse only inline elements:

```php
$parser = new \softark\creole\Creole();
$outputHtml = $parser->parse($wikiText);

// parse only inline elements (useful for one-line descriptions)
$parser = new \softark\creole\Creole();
$outputHtml = $parser->parseParagraph($wikiText);
```
You may optionally set the following option on the parser object:

- `$parser->html5 = false` to enable HTML4 output instead of HTML5.

And you should set the following properties when you are using the wiki style links, i.e. [[link]] for an internal link
and [[WikiName:link]] for an external link:

- `$parser->wikiUrl = 'http://www.example.com/wiki/'` for the url of the current wiki.
- `$parser->externalWikis = ['Wiki-A' => 'http://www.wiki-a.com/', 'Wiki-B' => 'http://www.wiki-b.net/']`
  for the external wikis. It should be an array in which the keys are the name of the wiki and
  the value the urls of them. 

It is recommended to use UTF-8 encoding for the input strings. Other encodings are currently not tested.

### Using raw html blocks

In the version 1.2.0 and later, you may optionally include raw html blocks in the source wiki text,
although this feature is disabled by default because it's not in the Creole 1.0 specification.

You can enable this feature by specifying the following option:

- `$parser->useRawHtml = true`

A raw html block should start with a line that only contains `<<<` and end with a corresponding closing line
which should be `>>>`, for example:
```
<<<
<p>This is a raw html block.</p>
<ul>
  <li>You can do whatever you want.</li>
  <li>You have to be responsible for the consequence.</li>
</ul>
>>>
```

Note that the output of the raw html blocks get **CLEANSED** automatically with `$parser->rawHtmlFilter` when it is specified.
A recommendation is to use HTML Purifier for the filter. For example:

```
$parser->rawHtmlFilter = function($input) {
    $config = \HTMLPurifier_Config::createDefault();
    $purifier = \HTMLPurifier::getInstance($config);
    return $purifier->purify($input);
};

// Or, if you are using Yii 2
$parser->rawHtmlFilter = function($input) {
    return \yii\helpers\HtmlPurifier::process($input);
};
```

As you see in the example, the `rawHtmlFilter` should be a callable that accepts the possibly unclean html 
text string and output the sanitized version of it. 

### The command line script

You can use it to convert a wiki text to a html file:

    bin/creole some.txt > some.html

Here is the full Help output you will see when running `bin/creole --help`:

    PHP Creole to HTML converter
    ----------------------------
    
    by Nobuo Kihara <softark@gmail.com>
    
    Usage:
        bin/creole [--full] [file.txt]
    
        --full    ouput a full HTML page with head and body. If not given, only the parsed markdown will be output.

        --help    shows this usage information.

        If no file is specified input will be read from STDIN.

    Examples:

        Render a file with original creole:

            bin/creole README.txt > README.html

    Convert the original creole description to html using STDIN:

        curl http://www.wikicreole.org/attach/Creole1.0TestCases/creole1.0test.txt | $cmd > creole.html


Acknowledgements <a name="ack"></a>
----------------

I'd like to thank [@cebe][] for creating [cebe/markdown][] library on which this work depends.

As its name describes, cebe/markdown is a markdown parser, but is also a well designed general
purpose markup language parser at the bottom on which you can implement parsers not only for different
"flavors" of markdown but also for different markup languages, Creole for instance.

[@cebe]: https://github.com/cebe "Carsten Brandt"
[cebe/markdown]: https://github.com/cebe/markdown "A super fast, highly extensible markdown parser for PHP"

FAQ <a name="faq"></a>
---

### Where do I report bugs or rendering issues?

Just [open an issue][] on github, post your creole code and describe the problem.
You may also attach screenshots of the rendered HTML result to describe your problem.

[open an issue]: https://github.com/softark/creole/issues/new


### Am I free to use this?

This library is open source and licensed under the [MIT License][]. This means that you can do whatever you want
with it as long as you mention my name and include the [license file][license]. Check the [license][] for details.

[MIT License]: http://opensource.org/licenses/MIT

[license]: https://github.com/softark/creole/blob/master/LICENSE

Contact
-------

Feel free to contact me using [email](mailto:softark@gmail.com) or [twitter](https://twitter.com/softark).
