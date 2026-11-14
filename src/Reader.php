<?php

namespace Alc\Opml;

use Alc\Opml\Entity\Category;
use Alc\Opml\Entity\Feed;
use Alc\Opml\Entity\Opml;
use Alc\Opml\Entity\OutlineInterface;
use Alc\Opml\Exception\InvalidOpmlException;

class Reader
{
    /**
     * Parses an OPML file.
     *
     * @throws InvalidOpmlException when the file is unreadable or is not a valid OPML document
     */
    public function read(string $file): Opml
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new InvalidOpmlException(\sprintf('The file "%s" does not exist or is not readable.', $file));
        }

        return $this->parse(file_get_contents($file));
    }

    /**
     * Parses an OPML document given as a string.
     *
     * @throws InvalidOpmlException when the content is not a valid OPML document
     */
    public function parse(string $content): Opml
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($content, options: \LIBXML_NONET);
            $error = libxml_get_last_error();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (false === $xml) {
            throw new InvalidOpmlException(\sprintf('Invalid XML: %s', $error ? trim($error->message) : 'unknown error'));
        }

        if ('opml' !== $xml->getName() || !isset($xml->body)) {
            throw new InvalidOpmlException('Not an OPML document: expected an <opml> root element with a <body>.');
        }

        $opml = new Opml();
        $opml->setTitle($this->nullIfEmpty($xml->head->title ?? null));

        foreach ($xml->body->outline as $outline) {
            $opml->addOutline($this->createOutline($outline));
        }

        return $opml;
    }

    private function createOutline(\SimpleXMLElement $outline): OutlineInterface
    {
        // "text" is the mandatory attribute, "title" is optional: each one falls back on the other.
        $text = $this->nullIfEmpty($outline['text']);
        $title = $this->nullIfEmpty($outline['title']);

        if (null !== $xmlUrl = $this->nullIfEmpty($outline['xmlUrl'])) {
            return new Feed()
                ->setText($text ?? $title)
                ->setTitle($title ?? $text)
                ->setType($this->nullIfEmpty($outline['type']))
                ->setXmlUrl($xmlUrl)
                ->setHtmlUrl($this->nullIfEmpty($outline['htmlUrl']))
                ->setDescription($this->nullIfEmpty($outline['description']));
        }

        $category = new Category()
            ->setText($text ?? $title)
            ->setTitle($title ?? $text);

        foreach ($outline->outline as $child) {
            $category->addOutline($this->createOutline($child));
        }

        return $category;
    }

    private function nullIfEmpty(?\SimpleXMLElement $value): ?string
    {
        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }
}
