<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Cli\Commands;

use MythicalDash\Cli\App;
use MythicalDash\Cli\CommandBuilder;

class ApplySEO extends App implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cmdInstance = self::getInstance();
        $indexPath = getcwd() . '/frontend/dist/index.html';

        if (!file_exists($indexPath)) {
            $cmdInstance->send('&cNothing found: index.html does not exist.');

            return;
        }

        $cmdInstance->send('&7Welcome to the MythicalDash Patch Manager!');
        $cmdInstance->send('&7This tool will help you improve your website\'s SEO.');
        $cmdInstance->send('');
        $cmdInstance->send('&ePlease answer the following questions. Press Enter to skip any field.');

        $fields = [
            [
                'label' => 'What do you want your website title to be?',
                'type' => 'title',
            ],
            [
                'label' => 'Describe your website in one sentence (meta description):',
                'type' => 'meta',
                'meta_name' => 'description',
            ],
            [
                'label' => 'Main keywords for your website (comma separated):',
                'type' => 'meta',
                'meta_name' => 'keywords',
            ],
            [
                'label' => 'Who is the author of this website?',
                'type' => 'meta',
                'meta_name' => 'author',
            ],
            [
                'label' => 'OpenGraph title (for social sharing):',
                'type' => 'meta',
                'meta_property' => 'og:title',
            ],
            [
                'label' => 'OpenGraph description (for social sharing):',
                'type' => 'meta',
                'meta_property' => 'og:description',
            ],
            [
                'label' => 'Twitter card title:',
                'type' => 'meta',
                'meta_name' => 'twitter:title',
            ],
            [
                'label' => 'Twitter card description:',
                'type' => 'meta',
                'meta_name' => 'twitter:description',
            ],
        ];

        $answers = [];
        foreach ($fields as $field) {
            $cmdInstance->send('');
            $cmdInstance->send('&e' . $field['label']);
            $answer = trim(fgets(STDIN));
            if ($answer !== '') {
                $answers[] = ['field' => $field, 'value' => $answer];
            }
        }

        if (empty($answers)) {
            $cmdInstance->send('&cNo SEO fields provided. Nothing to do.');

            return;
        }

        // Load and patch HTML
        libxml_use_internal_errors(true);
        $html = file_get_contents($indexPath);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $head = $dom->getElementsByTagName('head')->item(0);
        $xpath = new \DOMXPath($dom);

        foreach ($answers as $item) {
            $field = $item['field'];
            $value = $item['value'];
            if ($field['type'] === 'title') {
                $titleNodes = $dom->getElementsByTagName('title');
                if ($titleNodes->length > 0) {
                    $titleNode = $titleNodes->item(0);
                    if ($titleNode instanceof \DOMElement && $titleNode->tagName === 'title') {
                        $titleNode->nodeValue = $value;
                    }
                } else {
                    $title = $dom->createElement('title', $value);
                    $head->appendChild($title);
                }
            } elseif ($field['type'] === 'meta') {
                if (isset($field['meta_name'])) {
                    $query = "//meta[@name='" . $field['meta_name'] . "']";
                    $nodes = $xpath->query($query);
                    if ($nodes->length > 0) {
                        $nodes->item(0)->setAttribute('content', $value);
                    } else {
                        $meta = $dom->createElement('meta');
                        $meta->setAttribute('name', $field['meta_name']);
                        $meta->setAttribute('content', $value);
                        $head->appendChild($meta);
                    }
                } elseif (isset($field['meta_property'])) {
                    $query = "//meta[@property='" . $field['meta_property'] . "']";
                    $nodes = $xpath->query($query);
                    if ($nodes->length > 0) {
                        $nodes->item(0)->setAttribute('content', $value);
                    } else {
                        $meta = $dom->createElement('meta');
                        $meta->setAttribute('property', $field['meta_property']);
                        $meta->setAttribute('content', $value);
                        $head->appendChild($meta);
                    }
                }
            }
        }

        $newHtml = $dom->saveHTML();
        if (file_put_contents($indexPath, $newHtml) === false) {
            $cmdInstance->send('&cFailed to write changes to index.html');

            return;
        }
        $cmdInstance->send('&aSEO patch applied to index.html successfully!');
    }

    public static function getDescription(): string
    {
        return 'Apply SEO to the frontend';
    }

    public static function getSubCommands(): array
    {
        return [];
    }
}
