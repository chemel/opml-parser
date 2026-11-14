<?php

namespace Alc\Opml\Entity;

class Feed implements OutlineInterface
{
    private ?int $id = null;
    private ?string $text = null;
    private ?string $title = null;
    private ?string $type = null;
    private ?string $xmlUrl = null;
    private ?string $htmlUrl = null;
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getXmlUrl(): ?string
    {
        return $this->xmlUrl;
    }

    public function setXmlUrl(string $xmlUrl): static
    {
        $this->xmlUrl = $xmlUrl;

        return $this;
    }

    public function getHtmlUrl(): ?string
    {
        return $this->htmlUrl;
    }

    public function setHtmlUrl(?string $htmlUrl): static
    {
        $this->htmlUrl = $htmlUrl;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
