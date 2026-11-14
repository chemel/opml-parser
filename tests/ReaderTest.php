<?php

namespace Alc\Opml\Tests;

use Alc\Opml\Entity\Category;
use Alc\Opml\Entity\Feed;
use Alc\Opml\Entity\Opml;
use Alc\Opml\Reader;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    private Opml $opml;

    protected function setUp(): void
    {
        $this->opml = new Reader()->read(__DIR__.'/opml.xml');
    }

    public function testReadsTheDocumentTitle(): void
    {
        $this->assertSame('Feeds of Alexis', $this->opml->getTitle());
    }

    public function testReadsTheTopLevelCategory(): void
    {
        $outlines = $this->opml->getOutlines();

        $this->assertCount(1, $outlines);
        $this->assertInstanceOf(Category::class, $outlines[0]);
        $this->assertSame('Blogs', $outlines[0]->getText());
        $this->assertSame('Blogs', $outlines[0]->getTitle());
    }

    public function testReadsTheFeedsOfTheCategory(): void
    {
        $category = $this->opml->getOutlines()[0];
        $this->assertInstanceOf(Category::class, $category);

        $feeds = $category->getOutlines();

        $this->assertCount(2, $feeds);
        $this->assertContainsOnlyInstancesOf(Feed::class, $feeds);
        $this->assertSame($feeds, $category->getFeeds());
    }

    public function testReadsTheFeedAttributes(): void
    {
        [$journalDuHacker, $korben] = $this->opml->getFeeds();

        $this->assertSame('Journal du Hacker', $journalDuHacker->getText());
        $this->assertSame('Journal du Hacker', $journalDuHacker->getTitle());
        $this->assertSame('rss', $journalDuHacker->getType());
        $this->assertSame('https://www.journalduhacker.net/rss', $journalDuHacker->getXmlUrl());
        $this->assertSame('http://www.journalduhacker.net/', $journalDuHacker->getHtmlUrl());
        $this->assertNull($journalDuHacker->getDescription());

        $this->assertSame('Korben', $korben->getText());
        $this->assertSame('Korben', $korben->getTitle());
        $this->assertSame('rss', $korben->getType());
        $this->assertSame('https://korben.info/feed', $korben->getXmlUrl());
        $this->assertSame('http://korben.info/', $korben->getHtmlUrl());
        $this->assertNull($korben->getDescription());
    }

    public function testCollectsAllFeedsOfTheDocument(): void
    {
        $this->assertCount(2, $this->opml->getFeeds());
    }
}
