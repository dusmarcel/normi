# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A DokuWiki Syntax Plugin named **normi** that automatically links references to individual articles of the German *Anerkennungsverordnung* (recognition ordinance) to their respective DokuWiki pages.

The plugin must be installed at `lib/plugins/normi/` inside a DokuWiki installation — the folder name must exactly match the plugin base name.

## Architecture

The core is the syntax component `syntax.php`. DokuWiki Syntax Plugins follow a fixed lifecycle:

1. **`getType()`** — declares the syntax type (`substitution` for inline replacements that don't nest)
2. **`getSort()`** — priority relative to other plugins; lower = higher priority
3. **`connectTo($mode)`** — registers a regex pattern with the DokuWiki Lexer; when matched, `handle()` is called
4. **`handle($match, $state, $pos, $handler)`** — parses the matched text and returns structured data passed to `render()`
5. **`render($mode, $renderer, $data)`** — outputs the final markup; `$mode` is `xhtml` for normal page rendering

The current pattern matched by the Lexer:
```
Art\. [0-9]+[a-z]? Anerkennungsverordnung
```
e.g. `Art. 5 Anerkennungsverordnung` or `Art. 12a Anerkennungsverordnung`.

Laws *without* wiki pages are handled by `ExternalLaws.php` (class `dokuwiki\plugin\normi\ExternalLaws`, autoloaded by DokuWiki): the Lexer only checks the *shape* of the law name (`ExternalLaws::lexerPattern()`), `resolve()` maps it to an abbreviation, and the link goes to dejure.org → buzer.de → gesetze-im-internet.de, whichever carries the law first (checked once per law over HTTP, cached as `*.normi` in DokuWiki's cache dir). Such regulations are passed around as `ext:p:<abbr>` (§) / `ext:a:<abbr>` (Art.); `normLink()` in `syntax.php` decides between wiki and external link.

`script.js`/`style.css` add hover previews of the linked wiki pages (via `do=export_xhtmlbody`).

### Lexer pitfalls

- DokuWiki combines all patterns of a mode — core modes (acronyms, smileys, entities) and *all* plugins — into **one** regex, and again into the closer scans of formatting modes ("opaque spans"); PCRE's compiled-size limit is close. Exceeding it shows "A PCRE internal error occured" on every page. Measure the headroom of the *real* combined regex (not normi's patterns alone) and keep several thousand characters spare, since production wikis add acronyms and plugins. Subroutine calls (`(?+n)`, `(?(R)…)`) can't be used to share sub-patterns: bare parentheses are escaped and the closer scans have no per-pattern groups. Every addition to a pattern that is embedded in several Lexer patterns costs multiple times. Bounded quantifiers like `{0,7}` on groups are unrolled and expensive.
- The Lexer compiles **without `/u`** (byte mode): don't put umlauts into character classes; spell them as alternatives or byte sequences (`\xC3[\x84\x96\x9C]`).
- `ParallelRegex::escapePattern()` escapes `/` itself — inside a character class write `/` **unescaped** (`[A-Za-z0-9/-]`), otherwise `\/` turns into a broken delimiter.

## Development

There are no build or test commands — this is a plain PHP file deployed directly into a DokuWiki installation. To test changes:

1. Place (or symlink) this repository into a DokuWiki install at `lib/plugins/normi/`
2. Edit a wiki page containing a matching pattern and preview it
3. DokuWiki has no caching for plugin PHP files in development mode; clear the page cache if needed via `?purge=true`

DokuWiki plugin development reference: https://www.dokuwiki.org/devel:syntax_plugins
