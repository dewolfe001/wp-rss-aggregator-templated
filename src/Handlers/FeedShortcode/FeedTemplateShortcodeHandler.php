<?php

namespace RebelCode\Wpra\Core\Handlers\FeedShortcode;

/**
 * Renders RSS/Atom items using shortcode inner content as an HTML template.
 */
class FeedTemplateShortcodeHandler
{
    /**
     * @param array $atts Shortcode attributes.
     * @param string|null $content Shortcode inner template.
     *
     * @return string
     */
    public function __invoke($atts = [], $content = null)
    {
        if (!is_string($content) || trim($content) === '') {
            return '';
        }

        $defaults = [
            'src' => '',
            'limit' => 5,
            'cache' => 1800,
            'empty' => '',
        ];

        $atts = shortcode_atts($defaults, is_array($atts) ? $atts : [], 'feed_template');
        $src = trim((string) $atts['src']);

        if ($src === '') {
            return apply_filters('wprss_feed_template_output_not_found', '<!-- wprss feed template source not found -->', $atts);
        }

        $limit = max(1, (int) $atts['limit']);
        $feed = fetch_feed($src);
        if (is_wp_error($feed)) {
            return apply_filters('wprss_feed_template_output_error', '<!-- wprss feed template load error -->', $feed, $atts);
        }

        $channel = [
            'title' => esc_html((string) $feed->get_title()),
            'link' => esc_url((string) $feed->get_link()),
            'description' => esc_html(wp_strip_all_tags((string) $feed->get_description())),
        ];

        $items = $feed->get_items(0, $limit);
        if (empty($items)) {
            $empty = (string) $atts['empty'];
            if ($empty === '') {
                $empty = apply_filters('wprss_feed_template_output_empty', '<!-- wprss feed template empty -->', $atts);
            }

            return $empty;
        }

        $template = apply_filters('wprss_feed_template_pre_render', (string) $content, $atts);
        $total = count($items);
        $output = '';

        foreach ($items as $index => $item) {
            $itemTitle = (string) $item->get_title();
            $itemLink = (string) $item->get_link();
            $itemDescription = (string) $item->get_description();
            $itemAuthor = $item->get_author();
            $authorName = $itemAuthor ? (string) $itemAuthor->get_name() : '';
            $categories = $item->get_categories();
            $categoryLabel = '';
            if (is_array($categories) && isset($categories[0])) {
                $categoryLabel = (string) $categories[0]->get_label();
            }

            $enclosure = $item->get_enclosure();
            $enclosureUrl = '';
            $enclosureType = '';
            $enclosureImage = '';
            if ($enclosure) {
                $enclosureUrl = (string) $enclosure->get_link();
                $enclosureType = strtolower((string) $enclosure->get_type());
                if ($enclosureUrl !== '' && strpos($enclosureType, 'image/') === 0) {
                    $enclosureImage = sprintf(
                        '<img src="%s" alt="%s" loading="lazy" />',
                        esc_url($enclosureUrl),
                        esc_attr($itemTitle)
                    );
                }
            }

            $timestamp = $item->get_date('U');
            $isoDate = $timestamp ? gmdate('c', (int) $timestamp) : '';
            $displayDate = $timestamp ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $timestamp) : '';

            $tokenValues = [
                '{title}' => esc_html($itemTitle),
                '{link}' => esc_url($itemLink),
                '{description}' => wp_kses_post($itemDescription),
                '{author}' => esc_html($authorName),
                '{category}' => esc_html($categoryLabel),
                '{guid}' => esc_html((string) $item->get_id()),
                '{pubDate}' => esc_html($displayDate),
                '{pubDate_iso}' => esc_attr($isoDate),
                '{source}' => esc_html((string) $item->get_source()),
                '{channel.title}' => $channel['title'],
                '{channel.link}' => $channel['link'],
                '{channel.description}' => $channel['description'],
                '{index}' => (string) $index,
                '{index1}' => (string) ($index + 1),
                '{is_first}' => $index === 0 ? '1' : '0',
                '{is_last}' => $index === $total - 1 ? '1' : '0',
                '{count}' => (string) $total,
                '{total}' => (string) $total,
                '{enclosure_url}' => esc_url($enclosureUrl),
                '{enclosure_type}' => esc_html($enclosureType),
                '{enclosure_image}' => $enclosureImage,
            ];

            foreach ($tokenValues as $token => $value) {
                $tokenValues[$token] = apply_filters('wprss_feed_template_token_value', $value, $token, $item, $atts);
            }

            $tokenValues = apply_filters('wprss_feed_template_token_map', $tokenValues, $item, $atts);
            uksort($tokenValues, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            $output .= str_replace(array_keys($tokenValues), array_values($tokenValues), $template);
        }

        return apply_filters('wprss_feed_template_output', do_shortcode($output), $atts);
    }
}
