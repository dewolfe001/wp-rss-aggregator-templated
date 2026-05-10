# Feed template shortcode

WP RSS Aggregator includes a template-based shortcode for rendering an external RSS or Atom feed directly from page or post content:

```text
[feed_template src="https://example.com/feed/" limit="5"]
    <article class="feed-card feed-card-{index1}">
        <h3><a href="{link}" target="_blank" rel="noopener">{title}</a></h3>
        <time datetime="{pubDate_iso}">{pubDate}</time>
        <div class="feed-card__summary">{description}</div>
    </article>
[/feed_template]
```

The content between the opening and closing shortcode tags is the item template. The shortcode fetches the feed from `src`, renders the template once for each feed item, replaces token placeholders such as `{title}` and `{link}`, then outputs the rendered markup.

## Shortcode attributes

| Attribute | Required | Default | Description |
| --- | --- | --- | --- |
| `src` | Yes | Empty | URL of the RSS or Atom feed to render. If omitted, the shortcode outputs a non-visible HTML comment. |
| `limit` | No | `5` | Maximum number of feed items to render. Values below `1` are treated as `1`. |
| `empty` | No | Empty | Optional markup to output when the feed loads successfully but contains no items. If omitted, the shortcode outputs a non-visible HTML comment. |
| `cache` | No | `1800` | Reserved for shortcode configuration compatibility. Feed fetching is handled by WordPress' feed API. |

The shortcode must use both opening and closing tags. If the inner template is empty, it returns no output.

## Available tokens

Use tokens inside the shortcode body. Tokens are replaced for each feed item before the final output is returned.

### Item tokens

| Token | Description | Escaping |
| --- | --- | --- |
| `{title}` | Feed item title. | HTML escaped. |
| `{itunes:title}` / `{itunes_title}` | iTunes item title from `<itunes:title>`, when present. | HTML escaped. |
| `{link}` | Feed item URL. | URL escaped. |
| `{description}` | Feed item description/content summary. | Sanitized with WordPress post HTML rules. |
| `{itunes:summary}` / `{itunes_summary}` | iTunes item summary from `<itunes:summary>`, when present. | Sanitized with WordPress post HTML rules. |
| `{content:encoded}` / `{content_encoded}` | Full content from `<content:encoded>`, when present. | Sanitized with WordPress post HTML rules. |
| `{author}` | Item author name, when available. | HTML escaped. |
| `{itunes:author}` / `{itunes_author}` | iTunes item author from `<itunes:author>`, when present. | HTML escaped. |
| `{category}` | First category label, when available. | HTML escaped. |
| `{guid}` | Item ID/GUID. | HTML escaped. |
| `{guid_isPermaLink}` | `isPermaLink` attribute from `<guid>`, when present. | HTML escaped. |
| `{pubDate}` | Item publication date formatted using the site's WordPress date and time settings. | HTML escaped. |
| `{pubDate_iso}` | Item publication date in ISO-8601 format for use in attributes such as `<time datetime="...">`. | Attribute escaped. |
| `{itunes:duration}` / `{itunes_duration}` | iTunes episode duration from `<itunes:duration>`, when present. | HTML escaped. |
| `{itunes:keywords}` / `{itunes_keywords}` | iTunes keywords from `<itunes:keywords>`, when present. | HTML escaped. |
| `{itunes:episode}` / `{itunes_episode}` | iTunes episode number from `<itunes:episode>`, when present. | HTML escaped. |
| `{itunes:episodeType}` / `{itunes_episodeType}` / `{itunes_episode_type}` | iTunes episode type from `<itunes:episodeType>`, when present. | HTML escaped. |
| `{itunes:explicit}` / `{itunes_explicit}` | iTunes explicit flag from `<itunes:explicit>`, when present. | HTML escaped. |
| `{source}` | Source value reported by the feed item, when available. | HTML escaped. |

### Channel tokens

| Token | Description | Escaping |
| --- | --- | --- |
| `{channel.title}` | Feed/channel title. | HTML escaped. |
| `{channel.link}` | Feed/channel link. | URL escaped. |
| `{channel.description}` | Feed/channel description with tags stripped. | HTML escaped. |

### Loop tokens

| Token | Description |
| --- | --- |
| `{index}` | Zero-based item position. The first rendered item is `0`. |
| `{index1}` | One-based item position. The first rendered item is `1`. |
| `{is_first}` | `1` for the first rendered item; `0` for all other items. |
| `{is_last}` | `1` for the last rendered item; `0` for all other items. |
| `{count}` | Total number of rendered items. |
| `{total}` | Same value as `{count}`. |

### Enclosure tokens

| Token | Description | Escaping |
| --- | --- | --- |
| `{enclosure_url}` | URL for the item's enclosure, when present. | URL escaped. |
| `{enclosure_length}` | Length attribute for the item's enclosure, when present. | HTML escaped. |
| `{enclosure_type}` | Lowercase media type for the enclosure, when present. | HTML escaped. |
| `{enclosure_image}` | Complete lazy-loaded `<img>` tag when the enclosure type starts with `image/`; otherwise empty. | The image URL and alt text are escaped before the tag is built. |

## Examples

### Minimal list

```text
<ul class="external-feed">
[feed_template src="https://example.com/feed/" limit="3"]
    <li><a href="{link}">{title}</a></li>
[/feed_template]
</ul>
```

### Cards with dates and excerpts

```text
<div class="feed-grid">
[feed_template src="https://example.com/feed/" limit="6"]
    <article class="feed-grid__item">
        {enclosure_image}
        <p class="feed-grid__meta">
            <span>{category}</span>
            <time datetime="{pubDate_iso}">{pubDate}</time>
        </p>
        <h3><a href="{link}" target="_blank" rel="noopener">{title}</a></h3>
        <div class="feed-grid__excerpt">{description}</div>
    </article>
[/feed_template]
</div>
```

### Empty-state message

```text
[feed_template src="https://example.com/feed/" limit="5" empty="<p>No feed items are available right now.</p>"]
    <h3><a href="{link}">{title}</a></h3>
[/feed_template]
```

### Nested shortcodes in the template

After token replacement, the shortcode runs the rendered output through WordPress shortcode processing once. This means you can place other shortcodes in the template body:

```text
[feed_template src="https://example.com/feed/" limit="2"]
    <section class="feed-promo">
        <h3><a href="{link}">{title}</a></h3>
        [button url="{link}"]Read item {index1}[/button]
    </section>
[/feed_template]
```

Nested shortcodes are processed after token replacement, so only use nested shortcodes that you trust in templates you control.

## Customization hooks

Developers can customize rendering with WordPress filters:

| Filter | Purpose |
| --- | --- |
| `wprss_feed_template_output_not_found` | Change the output when `src` is missing. Receives the default output and shortcode attributes. |
| `wprss_feed_template_output_error` | Change the output when the feed cannot be loaded. Receives the default output, the `WP_Error`, and shortcode attributes. |
| `wprss_feed_template_output_empty` | Change the default empty-feed output. Receives the default output and shortcode attributes. |
| `wprss_feed_template_pre_render` | Modify the inner template before token replacement. Receives the template and shortcode attributes. |
| `wprss_feed_template_token_value` | Modify a single token value. Receives the value, token name, feed item object, and shortcode attributes. |
| `wprss_feed_template_token_map` | Add, remove, or modify the full token map for an item. Receives the token map, feed item object, and shortcode attributes. |
| `wprss_feed_template_output` | Modify the final rendered output. Receives the output and shortcode attributes. |

Example: add a custom `{source_domain}` token.

```php
add_filter('wprss_feed_template_token_map', function ($tokens, $item, $atts) {
    $host = wp_parse_url((string) $item->get_link(), PHP_URL_HOST);
    $tokens['{source_domain}'] = esc_html($host ?: '');

    return $tokens;
}, 10, 3);
```

## Notes and limitations

- The shortcode is intentionally simple: it replaces tokens; it does not support conditionals, loops inside the template, arithmetic, or expression syntax.
- The inner template is considered authored site content. Feed values are treated as external data and escaped or sanitized before output.
- Unknown tokens are left unchanged in the output.
- Token replacement uses longest-token-first ordering, so similarly named tokens do not corrupt each other.
