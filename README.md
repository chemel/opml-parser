# opml-parser

A small PHP library to read and write [OPML](http://opml.org/) files, the format feed readers use to import and export lists of RSS/Atom subscriptions.

- **Reader**: parses an OPML file or string into plain PHP objects.
- **Writer**: turns those objects back into an OPML 1.0 document.
- No runtime dependencies besides PHP's SimpleXML extension.

## Requirements

- PHP 8.4 or later
- The `simplexml` and `libxml` extensions (enabled by default in most PHP builds)

## Installation

```bash
composer require alc/opml-parser
```

## Reading an OPML file

Given this `feeds.opml` file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.0">
  <head>
    <title>Feeds of Alexis</title>
  </head>
  <body>
    <outline text="Blogs" title="Blogs">
      <outline text="Journal du Hacker" title="Journal du Hacker" type="rss" xmlUrl="https://www.journalduhacker.net/rss" htmlUrl="http://www.journalduhacker.net/"/>
      <outline text="Korben" title="Korben" type="rss" xmlUrl="https://korben.info/feed" htmlUrl="http://korben.info/"/>
    </outline>
  </body>
</opml>
```

List every feed of the document, whatever its nesting level:

```php
use Alc\Opml\Reader;

$opml = new Reader()->read('feeds.opml');

echo $opml->getTitle(), "\n"; // Feeds of Alexis

foreach ($opml->getFeeds() as $feed) {
    echo $feed->getTitle(), ' - ', $feed->getXmlUrl(), "\n";
}
// Journal du Hacker - https://www.journalduhacker.net/rss
// Korben - https://korben.info/feed
```

### Parsing a string

Use `parse()` when the OPML content does not come from a local file, for example an upload or an HTTP response:

```php
use Alc\Opml\Reader;

$opml = new Reader()->parse($content);
```

### Walking the category tree

`getOutlines()` returns the direct children only, so you can keep the folder structure. Each outline is either a `Feed` or a `Category`:

```php
use Alc\Opml\Entity\Category;
use Alc\Opml\Entity\Feed;
use Alc\Opml\Entity\OutlineInterface;
use Alc\Opml\Reader;

function printOutline(OutlineInterface $outline, int $depth = 0): void
{
    $indent = str_repeat('  ', $depth);

    if ($outline instanceof Feed) {
        echo $indent, '- ', $outline->getTitle(), ' (', $outline->getXmlUrl(), ")\n";

        return;
    }

    if ($outline instanceof Category) {
        echo $indent, $outline->getTitle(), "/\n";
        foreach ($outline->getOutlines() as $child) {
            printOutline($child, $depth + 1);
        }
    }
}

$opml = new Reader()->read('feeds.opml');

foreach ($opml->getOutlines() as $outline) {
    printOutline($outline);
}
// Blogs/
//   - Journal du Hacker (https://www.journalduhacker.net/rss)
//   - Korben (https://korben.info/feed)
```

A category also has its own `getFeeds()`, which returns all the feeds below it, sub-categories included.

### Handling errors

`read()` and `parse()` throw an `InvalidOpmlException` when the file is missing or unreadable, when the content is not valid XML, or when it is not an OPML document (no `<opml>` root element or no `<body>`):

```php
use Alc\Opml\Exception\InvalidOpmlException;
use Alc\Opml\Reader;

try {
    $opml = new Reader()->read('feeds.opml');
} catch (InvalidOpmlException $e) {
    echo 'Could not read the OPML file: ', $e->getMessage(), "\n";
}
```

## Writing an OPML file

Build the document with the entities, then pass it to the `Writer`. All setters are fluent:

```php
use Alc\Opml\Entity\Category;
use Alc\Opml\Entity\Feed;
use Alc\Opml\Entity\Opml;
use Alc\Opml\Writer;

$opml = new Opml()
    ->setTitle('My feeds')
    ->addOutline(
        new Category()
            ->setText('Blogs')
            ->setTitle('Blogs')
            ->addOutline(
                new Feed()
                    ->setText('Korben')
                    ->setTitle('Korben')
                    ->setType('rss')
                    ->setXmlUrl('https://korben.info/feed')
                    ->setHtmlUrl('http://korben.info/')
            )
    );

$xml = new Writer()->write($opml);

file_put_contents('feeds.opml', $xml);
```

`write()` returns the XML as a string. The result, indented here for readability:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.0">
  <head>
    <title>My feeds</title>
  </head>
  <body>
    <outline text="Blogs" title="Blogs">
      <outline text="Korben" title="Korben" type="rss" xmlUrl="https://korben.info/feed" htmlUrl="http://korben.info/"/>
    </outline>
  </body>
</opml>
```

### Reading, editing and writing back

```php
use Alc\Opml\Entity\Feed;
use Alc\Opml\Reader;
use Alc\Opml\Writer;

$opml = new Reader()->read('feeds.opml');

$opml->addOutline(
    new Feed()
        ->setText('PHP.net news')
        ->setTitle('PHP.net news')
        ->setType('rss')
        ->setXmlUrl('https://www.php.net/feed.atom')
);

file_put_contents('feeds.opml', new Writer()->write($opml));
```

## Data model

| Class | Represents | Main methods |
| --- | --- | --- |
| `Opml` | The whole document | `getTitle()`, `getOutlines()`, `getFeeds()`, `addOutline()` |
| `Category` | An `<outline>` that groups other outlines | `getText()`, `getTitle()`, `getOutlines()`, `getFeeds()`, `addOutline()` |
| `Feed` | An `<outline>` with an `xmlUrl` attribute | `getText()`, `getTitle()`, `getType()`, `getXmlUrl()`, `getHtmlUrl()`, `getDescription()` |

Both `Category` and `Feed` implement `OutlineInterface`. All classes live in the `Alc\Opml\Entity` namespace.

Notes on how the Reader maps the XML:

- An `<outline>` with a non-empty `xmlUrl` is a `Feed`; any other `<outline>` is a `Category`.
- `text` and `title` fall back on each other when one of them is missing.
- Missing or empty attributes are returned as `null`.

## Tests

```bash
composer install
vendor/bin/phpunit
```

## License

MIT. See [LICENSE](LICENSE).
