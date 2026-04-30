# PRD: Template-in-Shortcode Pattern

**Author:** Shawn DeWolfe, Web321 Marketing Ltd. **Date:** April 30, 2026 **Status:** Draft v2.0 **Origin:** Pattern first implemented in GF REST Submissions (`[gfrs_output]`). This document specifies it as a domain-agnostic pattern. First reuse target: an RSS feed renderer.

---

## 1\. Overview

A shortcode pattern in which the **content between the opening and closing tags is treated as an HTML template**. The plugin parses that template for token placeholders, resolves them against an arbitrary data source, and emits the rendered HTML in place of the shortcode.

The pattern is independent of what the data source is. It works for:

- A single record (a form submission, a WooCommerce order, the current user)  
- A collection of records (an RSS feed, a custom WP\_Query, an external API list)  
- A structured object (a parsed JSON document, a configuration blob)

The plugin author specifies **how data is fetched** and **what tokens it exposes**. The site builder specifies **what the output looks like**. The pattern handles the substitution mechanics in the middle.

**Pattern shape — single record:**

\[plugin\_output context-attrs\]

  \<div class="card"\>

    \<h2\>{title}\</h2\>

    \<p\>{summary}\</p\>

  \</div\>

\[/plugin\_output\]

**Pattern shape — collection:**

\[plugin\_output source-attrs limit="5"\]

  \<article\>

    \<h3\>\<a href="{link}"\>{title}\</a\>\</h3\>

    \<p\>{description}\</p\>

    \<small\>{pubDate}\</small\>

  \</article\>

\[/plugin\_output\]

In the collection case the inner template renders once per item; outputs are concatenated.

---

## 2\. Problem Statement

Plugins that expose dynamic data on the front end almost always make one of three unsatisfying choices:

1. **Hardcoded HTML output** — the plugin emits a fixed structure with maybe a few CSS hooks. Restyling is overrides on top of overrides; restructuring requires forking the plugin.  
2. **PHP template overrides** — the plugin asks users to copy a template file into their theme. This is fragile, requires PHP, and is invisible to page builders.  
3. **Attribute-driven micro-formats** — the plugin grows a long list of `format=`, `show-date=`, `class=` attributes that never quite cover what a given site needs.

Site builders working in Breakdance, Elementor, or the block editor want a fourth option: **drop a shortcode, write HTML inside it, get rendered output**. No theme files, no PHP, no plugin forking, no attribute soup.

This PRD describes that fourth option as a reusable pattern any Web321 plugin can adopt.

---

## 3\. Goals

- **Builder-friendly** — works in any page builder that supports shortcode blocks.  
- **No PHP for end users** — templating happens in page content.  
- **Layout-agnostic** — plugin supplies data, user supplies markup.  
- **Source-agnostic** — works for single records, collections, and structured objects without a separate pattern for each.  
- **Discoverable tokens** — every available token is documented in the plugin's settings page with copy-pasteable examples.  
- **Safe by default** — values are escaped according to context, with hooks to opt out where raw HTML is needed.  
- **Extensible** — filter hooks let third parties add tokens, change source parameters, and customise error states.

## 4\. Non-Goals

- Not a full template engine. No conditionals, no expressions, no arithmetic. If the use case needs `{% if %}` or `{% for x in y %}`, reach for Twig/Timber/Blade.  
- Not a replacement for block templates, theme.json, or full-site editing.  
- Caching strategy is per-implementation, not part of this pattern.  
- Not concerned with where data comes from — that's the plugin's job.

---

## 5\. First Reuse Target: RSS Feed Renderer

To keep this PRD grounded, a concrete first implementation: a shortcode that fetches an RSS or Atom feed and renders its items using a user-supplied template.

\[feed\_template src="https://example.com/feed/" limit="5" cache="1800"\]

  \<article class="feed-item"\>

    \<h3\>\<a href="{link}" target="\_blank" rel="noopener"\>{title}\</a\>\</h3\>

    \<p\>{description}\</p\>

    \<footer\>

      \<time datetime="{pubDate\_iso}"\>{pubDate}\</time\>

      \<span\>{author}\</span\>

    \</footer\>

  \</article\>

\[/feed\_template\]

Token families this exercises:

| Family | Examples | Notes |
| :---- | :---- | :---- |
| Item data | `{title}`, `{link}`, `{description}`, `{guid}` | Standard RSS/Atom fields |
| Item metadata | `{pubDate}`, `{pubDate_iso}`, `{author}`, `{category}` | Formatted variants live alongside raw |
| Channel data | `{channel.title}`, `{channel.link}` | Dot notation for nested object access |
| Iteration | `{index}`, `{index1}`, `{is_first}`, `{is_last}` | Provided by the framework, not the source |

This implementation **drives the shape of the abstract pattern** described below — every requirement should be checkable against this RSS use case.

---

## 6\. Functional Requirements

### 6.1 Shortcode Structure

The shortcode MUST be enclosed (open \+ close tags) so the WordPress shortcode API captures inner content as `$content`.

add\_shortcode( 'plugin\_output', \[ Plugin\_Shortcode::class, 'render' \] );

public static function render( $atts, ?string $content \= null ): string { ... }

If `$content` is empty or null, the shortcode returns an empty string immediately — no error, no fallback rendering.

### 6.2 Rendering Modes

The pattern supports two modes; the implementation declares which it produces.

**Single mode** — one render of the template, with one token map.

- Use case: form-submission confirmation, current-user dashboard, single-order receipt.  
- The data source resolves to one object or returns null.

**Collection mode** — N renders of the template, one per item, concatenated.

- Use case: RSS feed, post list, search results, API response array.  
- The data source resolves to an iterable of objects (possibly empty).  
- A `limit` attribute SHOULD be available to cap iteration count.  
- An optional `wrapper-before` / `wrapper-after` attribute pair MAY be supported for surrounding markup that should appear once.  
- An optional `separator` attribute MAY be supported for output between items.

A single plugin MAY offer both modes through different shortcode names (e.g. `[feed_channel]` for single, `[feed_template]` for collection) or through a single shortcode whose data source determines mode automatically.

### 6.3 Source Resolution

Before tokens can be resolved, the plugin MUST identify the data source. Resolution strategies, in priority order:

1. **Shortcode attributes** — `[feed_template src="..."]`. The most explicit and most common for collections.  
2. **URL query parameter** — `?hash_id=...` or `?order=...`. Common for single-record post-action pages. The parameter name MUST be filterable.  
3. **Implicit context** — `get_the_ID()`, `wp_get_current_user()`, "the most recent X". Domain-dependent.

If no source can be resolved, the shortcode returns a filterable "not found" string (default: an HTML comment so the page doesn't visibly break).

### 6.4 Token Syntax

Tokens use curly braces around a name: `{token_name}`. The braces and contents together form the replacement key.

**Naming conventions** the pattern endorses (each plugin picks what fits its domain):

| Convention | Example | When to use |
| :---- | :---- | :---- |
| Bare token | `{title}` | Top-level data on the current item |
| Dot notation | `{author.name}` | Nested object access |
| Prefix namespace | `{meta_pubDate}` | Source-level metadata (vs item data) |
| Suffix variant | `{pubDate_iso}` | Alternative formats of the same value |
| Iteration token | `{index}`, `{is_first}` | Framework-provided, not source-provided |
| Disambiguator | `{Billing - City}` | Collision resolution where two tokens share a base name |

**Case sensitivity:** human-readable label tokens (where applicable) MUST be matched case-insensitively. Both `{First Name}` and `{first name}` resolve identically. Implementation registers both an exact-case and lowercased key in the token map.

**Reserved prefix:** the iteration tokens (`index`, `index1`, `is_first`, `is_last`, `count`, `total`) MUST be reserved by the framework in collection mode and never overwritten by source data.

### 6.5 Token Map Construction

The plugin builds a token map (`array<string, string>`) where keys are the full bracketed token (`{title}`) and values are the escaped, ready-to-output strings.

**In single mode:** the map is built once.

**In collection mode:** the map is rebuilt per iteration, with framework-provided iteration tokens added on top of source-provided tokens.

**Build order within a single map:**

1. Identity tokens (stable IDs, hashes).  
2. Source-level metadata tokens (the channel data in RSS; the form definition in GF).  
3. Item data tokens (the simplest case — bare names).  
4. Formatted-variant tokens (`_iso`, `_human`, `_short` etc.).  
5. Iteration tokens (collection mode only).  
6. Anything added via filter.

**First-writer-wins** within a single map: if two registrations target the same key, the first wins. This forces explicit disambiguation rather than silent overwrite.

### 6.6 Replacement Algorithm

Tokens MUST be replaced using **longest-match-first** ordering to prevent substring collisions:

uksort( $tokens, fn( $a, $b ) \=\> strlen( $b ) \- strlen( $a ) );

return str\_replace( array\_keys( $tokens ), array\_values( $tokens ), $template );

Without descending-length sort, `{title}` would partially match inside `{title_short}` and corrupt output. This rule is non-negotiable and must be lifted verbatim into every implementation.

### 6.7 Nested Shortcode Support

After token replacement, the rendered output MUST be passed through `do_shortcode()` once so users can embed other shortcodes in their templates:

return do\_shortcode( $output );

**Critical security boundary:** `do_shortcode()` runs on the **template after substitution**, NEVER on the resolved values themselves. Substituted values are escaped strings — they must not be interpreted as shortcodes. If a feed item's title contains `[evil_shortcode]`, that text must render literally in the output, not execute.

### 6.8 Output Escaping

Values MUST be escaped at token-map-build time, not at replacement time. Default escape function depends on data type:

| Data type | Escape function |
| :---- | :---- |
| Plain text | `esc_html()` |
| URL | `esc_url()` |
| HTML attribute | `esc_attr()` |
| Pre-formatted HTML (opt-in only) | `wp_kses_post()` |
| Integer | `(string) (int)` |
| Datetime | Localised via `wp_date()` then `esc_html()` |

A filter (`{plugin}_token_value`) MUST allow per-token override — necessary, for example, if an RSS `description` field contains intentional HTML the user wants rendered.

### 6.9 Empty Source Handling

In collection mode, an empty source (zero items) MUST return a filterable "empty state" string rather than an empty render. Defaults to an HTML comment; site builders override via filter or via an optional `empty=""` shortcode attribute.

---

## 7\. Filter & Hook API

Every implementation MUST expose, at minimum:

| Filter | Purpose |
| :---- | :---- |
| `{plugin}_source_param` | Change the URL query parameter used for source resolution |
| `{plugin}_token_value` | Modify any resolved token value before it enters the map |
| `{plugin}_token_map` | Add, remove, or modify tokens before replacement |
| `{plugin}_output_not_found` | HTML returned when no source can be resolved |
| `{plugin}_output_error` | HTML returned when source resolves but data load fails |
| `{plugin}_output_empty` | HTML returned in collection mode when iteration yields zero items |
| `{plugin}_template_pre_render` | Mutate the template string before token replacement |
| `{plugin}_output` | Final filter on the fully-rendered HTML |

Collection-mode implementations additionally expose:

| Filter | Purpose |
| :---- | :---- |
| `{plugin}_collection` | Modify the array of items before iteration (sort, filter, slice) |
| `{plugin}_item_pre_render` | Modify a single item before its token map is built |

---

## 8\. Error Handling

The shortcode MUST never throw a fatal error or output a stack trace. All error states resolve to a filterable string defaulting to a non-breaking HTML comment:

- **No source** — `<!-- {plugin}: no source -->`  
- **Invalid source** — `<!-- {plugin}: invalid source -->`  
- **Source unreachable** (network, missing record) — `<!-- {plugin}: source unavailable -->`  
- **Permission denied** — `<!-- {plugin}: access denied -->`  
- **Empty collection** — `<!-- {plugin}: no items -->`

Administrators with `manage_options` MAY see verbose messages via opt-in constant.

---

## 9\. Security Considerations

### 9.1 Source Sanitisation

URL parameters used for source resolution MUST pass through `sanitize_text_field()`. Numeric IDs MUST validate as positive integers. External URLs (RSS sources, API endpoints) MUST be validated with `wp_http_validate_url()` and SHOULD be restricted to an allowlist via filter.

### 9.2 External Fetches

For sources that require HTTP requests (RSS feeds, REST APIs):

- Use `wp_safe_remote_get()` rather than `wp_remote_get()` — the safe variant blocks requests to local IPs and prevents SSRF.  
- Set a sensible timeout (5–10 seconds default).  
- Cache responses via `set_transient()`. Default TTL configurable per shortcode via attribute.  
- Respect HTTP cache headers where the source provides them.

### 9.3 Capability Checks

If the underlying data has access controls (private posts, restricted member data, draft entries), the shortcode MUST respect them. A hash-guessing or ID-iterating attacker must not be able to view data they shouldn't.

### 9.4 Output Escaping

See §6.8. Default escapes everything; raw HTML is opt-in per token via filter, never via shortcode attribute (an attribute would let any contributor bypass escaping).

### 9.5 Template as Trusted Source

The template comes from page content authored by users with `edit_posts` or higher. This is treated as trusted. Resolved values are NOT trusted — see §6.7.

---

## 10\. Discoverability

### 10.1 Settings Page Documentation

Every implementation MUST include, on the plugin's settings page:

1. A **complete token reference table** — every token with a short description.  
2. A **copy-paste example** of a working shortcode block.  
3. A **live preview tool** where the user can pick a source (or see a sample) and watch the tokens resolve.

### 10.2 Auto-Generated Docs Page

Per Shawn's standing plugin requirements, the plugin's auto-generated public-facing documentation page MUST include a "Templating" section covering:

- Token syntax overview  
- Full token reference table  
- Three or more usage examples (minimal, typical, advanced with nested shortcodes)  
- Notes on disambiguation and reserved tokens  
- Mode-specific notes (single vs collection) where applicable

---

## 11\. Implementation Architecture

A reference class structure for any plugin adopting this pattern:

class {Plugin}\_Shortcode {

    public static function init(): void;

    public static function render( $atts, $content );

    // Source-specific (every plugin specialises these):

    private static function resolve\_source( array $atts ): mixed;

    private static function load\_data( mixed $source ): mixed;

    private static function build\_token\_map( $item, array $context \= \[\] ): array;

    // Mechanical (lifted verbatim across plugins):

    private static function replace\_tokens( string $template, array $tokens ): string;

    private static function register\_label\_token( array &$map, string $label, string $value ): void;

    private static function add\_iteration\_tokens( array &$map, int $index, int $total ): void;

    private static function format\_value( mixed $raw, string $type ): string;

}

The mechanical helpers are strong candidates for a `class-template-shortcode-base.php` file copied into each plugin's `vendor/` directory — meeting the no-Composer preference while avoiding code duplication across the Web321 plugin family.

---

## 12\. Candidate Targets

The pattern fits any plugin or feature that surfaces structured data in WordPress:

| Domain | Mode | Source | Sample shortcode |
| :---- | :---- | :---- | :---- |
| **RSS / Atom feeds** (first reuse) | Collection | Feed URL | `[feed_template src="..."]` |
| WooCommerce orders | Single | Order key from URL | `[wc_order_output]` |
| MemberPress / WC Memberships | Single | Current user | `[member_output]` |
| WP\_Query results | Collection | Query args as attrs | `[posts_template type="post"]` |
| External REST APIs | Either | Endpoint URL | `[api_template src="..."]` |
| ACF fields on a post | Single | Current post | `[acf_output]` |
| WP Booking System | Single | Booking reference | `[wpbs_output]` |
| Press Releaser | Either | Release ID or list | `[pr_output]` / `[pr_list]` |
| Square API Platform | Single | Customer hash | `[square_customer_output]` |
| Saved searches / filters | Collection | Filter params | `[search_template]` |
| Meetup/iCal feeds | Collection | iCal URL | `[ical_template]` |
| GitHub repo / release info | Single or Collection | repo path | `[gh_template]` |

---

## 13\. Acceptance Criteria

A plugin implementing this pattern is considered complete when:

- [ ] The shortcode is enclosed (open \+ close) and captures `$content`.  
- [ ] Empty `$content` returns an empty string without error.  
- [ ] Source resolution works for all supported strategies (attribute, URL param, implicit).  
- [ ] Single-mode plugins render once; collection-mode plugins render per item.  
- [ ] Collection mode supports a `limit` attribute.  
- [ ] Token replacement uses longest-match-first ordering.  
- [ ] Reserved iteration tokens (`index`, `is_first`, etc.) cannot be overwritten by source data.  
- [ ] All values are appropriately escaped by default; raw-HTML opt-in is filter-only, not attribute.  
- [ ] All required filter hooks are implemented and documented.  
- [ ] Error states return filterable HTML comments, never fatals.  
- [ ] Empty collections return a filterable empty state.  
- [ ] Nested shortcodes inside the template are processed via `do_shortcode()`.  
- [ ] Resolved values are NOT processed as shortcodes.  
- [ ] External HTTP requests use `wp_safe_remote_get()` with a timeout and transient cache.  
- [ ] Settings page includes token reference table, example, and live preview.  
- [ ] Auto-generated docs page includes a "Templating" section.  
- [ ] At least three usage examples are documented.

---

## 14\. Out of Scope (this version)

These are deferred to a possible v3 once the pattern has shipped in three or more plugins:

- Conditional rendering (`{if has_value}...{/if}`).  
- Format modifiers (`{date|format:Y-m-d}`).  
- Token preview UI inside the page editor.  
- Auto-detection of the closest token name on typo (`{titel}` → suggest `{title}`).  
- Multi-source composition (rendering one feed's items wrapped in another's channel data).

---

## 15\. Open Questions

1. **Multi-value tokens.** When a single token resolves to an array (RSS categories, GF checkbox groups), do we (a) implode with `,` , (b) require the user to write `{categories.0}, {categories.1}`, or (c) introduce a `{categories|join:", "}` modifier? Option (a) is the lowest-friction default and what the first RSS implementation should do; (c) is a v3 feature.  
     
2. **Single base class vs copy-in helpers.** A shared `class-template-shortcode-base.php` lifted into each plugin's `vendor/` directory keeps mechanical code identical across the Web321 plugin family without becoming a runtime dependency. Worth building once before the second plugin uses the pattern. Recommend yes.  
     
3. **Naming convention.** Settle on `[{plugin}_output]` or `[{plugin}_template]` as the Web321 standard? Collections feel more natural with `_template`; single-record cases are clearer with `_output`. May need both.  
     
4. **Cache key strategy for external sources.** In the RSS implementation, what does the transient key look like? `feed_template_<md5(url+limit+sort)>` is a reasonable default but should be specified before implementation begins.  
     
5. **Failure visibility for editors.** When a feed is unreachable, an HTML comment hides the problem from front-end visitors but also hides it from the page editor previewing the page. Should logged-in editors see a visible error state by default? Probably yes.

