<?php

namespace Alc\Opml\Entity;

class Category implements OutlineInterface
{
    private ?string $text = null;
    private ?string $title = null;

    /** @var list<OutlineInterface> */
    private array $outlines = [];

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

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
     * Direct children: feeds and sub-categories.
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
     * All feeds of this category, sub-categories included.
     *
     * @return list<Feed>
     */
    public function getFeeds(): array
    {
        $feeds = [];
        foreach ($this->outlines as $outline) {
            if ($outline instanceof Feed) {
                $feeds[] = $outline;
            } elseif ($outline instanceof self) {
                array_push($feeds, ...$outline->getFeeds());
            }
        }

        return $feeds;
    }
}
