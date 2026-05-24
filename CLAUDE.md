# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A DokuWiki Syntax Plugin named **normi** that automatically links references to individual articles of the German *Anerkennungsverordnung* (recognition ordinance) to their respective DokuWiki pages.

The plugin must be installed at `lib/plugins/normi/` inside a DokuWiki installation — the folder name must exactly match the plugin base name.

## Architecture

This is a single-file DokuWiki plugin (`syntax.php`). DokuWiki Syntax Plugins follow a fixed lifecycle:

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

**Current state:** `render()` is a stub — it returns `true` for `xhtml` mode without emitting any output. The link generation to the target DokuWiki pages still needs to be implemented.

## Development

There are no build or test commands — this is a plain PHP file deployed directly into a DokuWiki installation. To test changes:

1. Place (or symlink) this repository into a DokuWiki install at `lib/plugins/normi/`
2. Edit a wiki page containing a matching pattern and preview it
3. DokuWiki has no caching for plugin PHP files in development mode; clear the page cache if needed via `?purge=true`

DokuWiki plugin development reference: https://www.dokuwiki.org/devel:syntax_plugins
