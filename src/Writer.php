<?php

namespace Alc\Opml;

/**
 * Serializes an Opml document to an OPML 1.0 XML string.
 *
 * This is the counterpart of the Reader:
 *
 *     $xml = new Writer()->write($opml);
 *     file_put_contents('feeds.opml', $xml);
 */
class Writer
{
    /**
     * Builds the XML of an OPML document.
     *
     * The document title goes in <head><title>, and each top-level outline
     * becomes an <outline> element of <body>, in the order they were added.
     *
     * @return string the XML document, including the XML declaration (UTF-8)
     */
    public function write(Entity\Opml $opml): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><opml version="1.0"></opml>');

        $head = $xml->addChild('head');
        // Property assignment escapes the value (addChild() does not escape "&").
        $head->title = $opml->getTitle() ?? '';

        $body = $xml->addChild('body');
        foreach ($opml->getOutlines() as $outline) {
            $this->writeOutline($body, $outline);
        }

        return $xml->asXML();
    }

    /**
     * Appends an <outline> element to $parent for the given outline.
     *
     * - A Category gets the "text" and "title" attributes, then its children
     *   are written recursively as nested <outline> elements.
     * - A Feed gets "text" and "title", plus "type", "xmlUrl" and "htmlUrl"
     *   when they are set. Its description is not written.
     */
    private function writeOutline(\SimpleXMLElement $parent, Entity\OutlineInterface $outline): void
    {
        $outlineElement = $parent->addChild('outline');
        if ($outline instanceof Entity\Category) {
            $outlineElement->addAttribute('text', $outline->getText() ?? '');
            $outlineElement->addAttribute('title', $outline->getTitle() ?? '');
            foreach ($outline->getOutlines() as $childOutline) {
                $this->writeOutline($outlineElement, $childOutline);
            }
        } elseif ($outline instanceof Entity\Feed) {
            $outlineElement->addAttribute('text', $outline->getText() ?? '');
            $outlineElement->addAttribute('title', $outline->getTitle() ?? '');
            if ($outline->getType()) {
                $outlineElement->addAttribute('type', $outline->getType());
            }
            if ($outline->getXmlUrl()) {
                $outlineElement->addAttribute('xmlUrl', $outline->getXmlUrl());
            }
            if ($outline->getHtmlUrl()) {
                $outlineElement->addAttribute('htmlUrl', $outline->getHtmlUrl());
            }
        }
    }
}