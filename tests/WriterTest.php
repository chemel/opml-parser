<?php

namespace Alc\Opml\Tests;

use Alc\Opml\Entity\Category;
use Alc\Opml\Entity\Feed;
use Alc\Opml\Entity\Opml;
use Alc\Opml\Reader;
use Alc\Opml\Writer;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    public function testWritesAnOpmlDocument(): void
    {
        $xml = $this->write($this->createOpml());

        $this->assertSame('opml', $xml->getName());
        $this->assertSame('1.0', (string) $xml['version']);
        $this->assertSame('Feeds of Alexis', (string) $xml->head->title);
        $this->assertCount(1, $xml->body->outline);
    }

    public function testWritesTheXmlDeclaration(): void
    {
        $content = new Writer()->write($this->createOpml());

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $content);
    }

    public function testWritesACategoryWithItsFeedsNested(): void
    {
        $category = $this->write($this->createOpml())->body->outline[0];

        $this->assertSame('Blogs', (string) $category['text']);
        $this->assertSame('Blogs', (string) $category['title']);
        $this->assertNull($category['xmlUrl']);
        $this->assertCount(2, $category->outline);
    }

    public function testWritesTheFeedAttributes(): void
    {
        $feed = $this->write($this->createOpml())->body->outline[0]->outline[0];

        $this->assertSame('Journal du Hacker', (string) $feed['text']);
        $this->assertSame('Journal du Hacker', (string) $feed['title']);
        $this->assertSame('rss', (string) $feed['type']);
        $this->assertSame('https://www.journalduhacker.net/rss', (string) $feed['xmlUrl']);
        $this->assertSame('http://www.journalduhacker.net/', (string) $feed['htmlUrl']);
    }

    public function testOmitsTheOptionalFeedAttributesWhenNotSet(): void
    {
        $opml = new Opml()->addOutline(
            new Feed()->setText('Korben')->setTitle('Korben')->setXmlUrl('https://korben.info/feed')
        );

        $feed = $this->write($opml)->body->outline[0];

        $this->assertSame('https://korben.info/feed', (string) $feed['xmlUrl']);
        $this->assertNull($feed['type']);
        $this->assertNull($feed['htmlUrl']);
    }

    public function testWritesTopLevelFeedsAndNestedCategoriesInOrder(): void
    {
        $opml = new Opml()
            ->addOutline(new Feed()->setText('First')->setTitle('First')->setXmlUrl('https://example.com/first'))
            ->addOutline(
                new Category()->setText('Parent')->setTitle('Parent')->addOutline(
                    new Category()->setText('Child')->setTitle('Child')->addOutline(
                        new Feed()->setText('Deep')->setTitle('Deep')->setXmlUrl('https://example.com/deep')
                    )
                )
            );

        $outlines = $this->write($opml)->body->outline;

        $this->assertCount(2, $outlines);
        $this->assertSame('First', (string) $outlines[0]['text']);
        $this->assertSame('Parent', (string) $outlines[1]['text']);
        $this->assertSame('Child', (string) $outlines[1]->outline[0]['text']);
        $this->assertSame('https://example.com/deep', (string) $outlines[1]->outline[0]->outline[0]['xmlUrl']);
    }

    public function testWritesADocumentWithoutOutlines(): void
    {
        $xml = $this->write(new Opml()->setTitle('Empty'));

        $this->assertSame('Empty', (string) $xml->head->title);
        $this->assertTrue(isset($xml->body));
        $this->assertCount(0, $xml->body->outline);
    }

    public function testWritesADocumentWithoutTitle(): void
    {
        $xml = $this->write(new Opml());

        $this->assertSame('', (string) $xml->head->title);
    }

    public function testPreservesSpecialCharacters(): void
    {
        $opml = new Opml()
            ->setTitle('Tom & "Jerry" <feeds>')
            ->addOutline(
                new Category()->setText('News & "Tech" <fr>')->setTitle("L'actu & co")->addOutline(
                    new Feed()
                        ->setText('A & B')
                        ->setTitle('A & B')
                        ->setType('rss')
                        ->setXmlUrl('https://example.com/feed?a=1&b=2')
                        ->setHtmlUrl('https://example.com/?a=1&b=2')
                )
            );

        $xml = $this->write($opml);
        $category = $xml->body->outline[0];
        $feed = $category->outline[0];

        $this->assertSame('Tom & "Jerry" <feeds>', (string) $xml->head->title);
        $this->assertSame('News & "Tech" <fr>', (string) $category['text']);
        $this->assertSame("L'actu & co", (string) $category['title']);
        $this->assertSame('A & B', (string) $feed['text']);
        $this->assertSame('https://example.com/feed?a=1&b=2', (string) $feed['xmlUrl']);
        $this->assertSame('https://example.com/?a=1&b=2', (string) $feed['htmlUrl']);
    }

    public function testTheReaderReadsBackWhatTheWriterWrote(): void
    {
        $reader = new Reader();
        $opml = $reader->read(__DIR__.'/opml.xml');

        $this->assertEquals($opml, $reader->parse(new Writer()->write($opml)));
    }

    /**
     * Same document as tests/opml.xml.
     */
    private function createOpml(): Opml
    {
        return new Opml()
            ->setTitle('Feeds of Alexis')
            ->addOutline(
                new Category()
                    ->setText('Blogs')
                    ->setTitle('Blogs')
                    ->addOutline(
                        new Feed()
                            ->setText('Journal du Hacker')
                            ->setTitle('Journal du Hacker')
                            ->setType('rss')
                            ->setXmlUrl('https://www.journalduhacker.net/rss')
                            ->setHtmlUrl('http://www.journalduhacker.net/')
                    )
                    ->addOutline(
                        new Feed()
                            ->setText('Korben')
                            ->setTitle('Korben')
                            ->setType('rss')
                            ->setXmlUrl('https://korben.info/feed')
                            ->setHtmlUrl('http://korben.info/')
                    )
            );
    }

    private function write(Opml $opml): \SimpleXMLElement
    {
        return new \SimpleXMLElement(new Writer()->write($opml));
    }
}
