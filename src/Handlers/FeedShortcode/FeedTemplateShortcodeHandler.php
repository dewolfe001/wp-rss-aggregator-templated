<?php

namespace RebelCode\Wpra\Core\Handlers\FeedShortcode;

/**
 * Renders RSS/Atom items using shortcode inner content as an HTML template.
 */
class FeedTemplateShortcodeHandler
{
    const NAMESPACE_ITUNES = 'http://www.itunes.com/dtds/podcast-1.0.dtd';
    const NAMESPACE_CONTENT = 'http://purl.org/rss/1.0/modules/content/';

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

            $itunesTitle = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'title');
            $itunesSummary = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'summary');
            $contentEncoded = self::getFirstItemTagData($item, [self::NAMESPACE_CONTENT], 'encoded');
            $itunesAuthor = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'author');
            $itunesDuration = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'duration');
            $itunesKeywords = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'keywords');
            $itunesEpisode = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'episode');
            $itunesEpisodeType = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'episodeType');
            $itunesExplicit = self::getFirstItemTagData($item, [self::NAMESPACE_ITUNES], 'explicit');
            $guidIsPermalink = self::getFirstItemTagAttribute($item, [''], 'guid', '', 'isPermaLink');

            $enclosure = $item->get_enclosure();
            $enclosureUrl = '';
            $enclosureType = '';
            $enclosureLength = '';
            $enclosureImage = '';
            if ($enclosure) {
                $enclosureUrl = (string) $enclosure->get_link();
                $enclosureType = strtolower((string) $enclosure->get_type());
                if (method_exists($enclosure, 'get_length')) {
                    $enclosureLength = (string) $enclosure->get_length();
                }
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
                '{itunes:title}' => esc_html($itunesTitle),
                '{itunes_title}' => esc_html($itunesTitle),
                '{link}' => esc_url($itemLink),
                '{description}' => wp_kses_post($itemDescription),
                '{itunes:summary}' => wp_kses_post($itunesSummary),
                '{itunes_summary}' => wp_kses_post($itunesSummary),
                '{content:encoded}' => wp_kses_post($contentEncoded),
                '{content_encoded}' => wp_kses_post($contentEncoded),
                '{author}' => esc_html($authorName),
                '{itunes:author}' => esc_html($itunesAuthor),
                '{itunes_author}' => esc_html($itunesAuthor),
                '{category}' => esc_html($categoryLabel),
                '{guid}' => esc_html((string) $item->get_id()),
                '{guid_isPermaLink}' => esc_html($guidIsPermalink),
                '{pubDate}' => esc_html($displayDate),
                '{pubDate_iso}' => esc_attr($isoDate),
                '{itunes:duration}' => esc_html($itunesDuration),
                '{itunes_duration}' => esc_html($itunesDuration),
                '{itunes:keywords}' => esc_html($itunesKeywords),
                '{itunes_keywords}' => esc_html($itunesKeywords),
                '{itunes:episode}' => esc_html($itunesEpisode),
                '{itunes_episode}' => esc_html($itunesEpisode),
                '{itunes:episodeType}' => esc_html($itunesEpisodeType),
                '{itunes_episodeType}' => esc_html($itunesEpisodeType),
                '{itunes_episode_type}' => esc_html($itunesEpisodeType),
                '{itunes:explicit}' => esc_html($itunesExplicit),
                '{itunes_explicit}' => esc_html($itunesExplicit),
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
                '{enclosure_length}' => esc_html($enclosureLength),
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

    /**
     * Retrieves the first matching XML item tag value.
     *
     * @param object $item       SimplePie item instance.
     * @param array  $namespaces XML namespaces to search.
     * @param string $tag        Tag name without prefix.
     *
     * @return string
     */
    private static function getFirstItemTagData($item, array $namespaces, $tag)
    {
        foreach ($namespaces as $namespace) {
            $tags = $item->get_item_tags($namespace, $tag);
            if (is_array($tags) && isset($tags[0]['data'])) {
                return (string) $tags[0]['data'];
            }
        }

        return '';
    }

    /**
     * Retrieves the first matching XML item tag attribute value.
     *
     * @param object $item               SimplePie item instance.
     * @param array  $namespaces         XML namespaces to search.
     * @param string $tag                Tag name without prefix.
     * @param string $attributeNamespace Attribute namespace.
     * @param string $attribute          Attribute name.
     *
     * @return string
     */
    private static function getFirstItemTagAttribute($item, array $namespaces, $tag, $attributeNamespace, $attribute)
    {
        foreach ($namespaces as $namespace) {
            $tags = $item->get_item_tags($namespace, $tag);
            if (is_array($tags) && isset($tags[0]['attribs'][$attributeNamespace][$attribute])) {
                return (string) $tags[0]['attribs'][$attributeNamespace][$attribute];
            }
        }

        return '';
    }

}
