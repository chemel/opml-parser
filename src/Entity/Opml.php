<?php

namespace Alc\Opml\Entity;

class Opml
{
    private ?string $title = null;

    /** @var list<OutlineInterface> */
    private array $outlines = [];

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Top-level outlines: feeds and categories.
     *
     * @return list<OutlineInterface>
     */
    public function getOutlines(): array
    {
        return $this->outlines;
    }

    public function addOutline(OutlineInterface $outline): static
    {
        $this->outlines[] = $outline;

        return $this;
    }

    /**
     * All feeds of the document, whatever their nesting level.
     *
     * @return list<Feed>
     */
    public function getFeeds(): array
    {
        $feeds = [];
        foreach ($this->outlines as $outline) {
            if ($outline instanceof Feed) {
                $feeds[] = $outline;
            } elseif ($outline instanceof Category) {
                array_push($feeds, ...$outline->getFeeds());
            }
        }

        return $feeds;
    }
}
