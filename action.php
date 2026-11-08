<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;

/**
 * normi: lets bare citations inherit the law of a following citation.
 *
 * In "nach § 12 Absatz 1 oder § 13 Absatz 1 sowie die nach § 26 Absatz 1 der
 * Deutschsprachförderverordnung" the law is named only once, after the last item; the bare
 * "§ 12 Absatz 1" and "§ 13 Absatz 1" belong to it too, not to the law of the current page.
 * syntax_plugin_normi::handle() already does this for plain paragraphs; this pass over the
 * finished call list also covers tables, list items and footnotes, whose calls are buffered
 * while they are handled. A run of bare citations ("__current__") joined to an explicit citation
 * of the same kind (§ or Artikel) only by connectors takes over its regulation.
 */
class action_plugin_normi extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('PARSER_HANDLER_DONE', 'BEFORE', $this, 'inheritRegulation');
        // DokuWiki versions that parse list items and blockquotes with a sub-handler
        $controller->register_hook('PARSER_SUBHANDLER_DONE', 'BEFORE', $this, 'inheritRegulation');
    }

    public function inheritRegulation(Event $event): void
    {
        $handler = $event->data;
        $handler->calls = $this->process($handler->calls);
    }

    private function process(array $calls): array
    {
        $pending = []; // indexes of the current run of bare citations
        $kind = null;  // their kind: "§" or "Art"
        $gap = null;   // text since the last citation of the run; null = no run

        foreach ($calls as $i => $call) {
            if ($call[0] === 'nest') {
                // e.g. footnotes: their own list, never continued across the boundary
                $calls[$i][1][0] = $this->process($call[1][0]);
                $pending = [];
                $gap = null;
                continue;
            }
            if ($call[0] === 'cdata') {
                if ($gap !== null) {
                    $gap .= $call[1][0];
                }
                continue;
            }
            $data = ($call[0] === 'plugin' && $call[1][0] === 'normi') ? $call[1][1] : null;
            $citationKind = $data === null ? null : syntax_plugin_normi::citationRunKind($data);
            if ($citationKind === null) {
                // any other markup, or a regulation name on its own: ends the run
                $pending = [];
                $gap = null;
                continue;
            }

            if ($gap === null || $citationKind !== $kind || !syntax_plugin_normi::joinsList($gap)) {
                $pending = [];
            }

            if ($data['regulation'] === '__current__') {
                $pending[] = $i;
                $kind = $citationKind;
                $gap = '';
                continue;
            }

            if ($data['regulation'] !== null) {
                foreach ($pending as $p) {
                    $calls[$p][1][1]['regulation'] = $data['regulation'];
                }
            }
            $pending = [];
            $gap = null;
        }
        return $calls;
    }
}
