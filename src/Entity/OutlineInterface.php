<?php

namespace Alc\Opml\Entity;

/**
 * An <outline> element of an OPML document: either a Feed or a Category.
 */
interface OutlineInterface
{
    public function getText(): ?string;

    public function setText(?string $text): static;

    public function getTitle(): ?string;

    public function setTitle(?string $title): static;
}
