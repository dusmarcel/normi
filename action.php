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
 * The Lexer sees each citation on its own, so this is done on the finished call list: a run of
 * bare citations ("__current__") that is joined to an explicit citation of the same kind (§ or
 * Artikel) only by connectors (",", "oder", "sowie die nach", …) takes over its regulation.
 */
class action_plugin_normi extends ActionPlugin
{
    /** Text allowed between two citations of one list: connectors, articles, prepositions. */
    private const GAP = '/^[,;]?(?: (?:und|oder|sowie|bzw\.|beziehungsweise|und\/oder|auch|ferner|die|der|das|den|dem|des|nach|gemäß|aus|in|im|von|zu|auf))* $/';
    /** …but the gap must contain an actual list connector. */
    private const CONNECTOR = '/^[,;]| (?:und|oder|sowie|bzw\.|beziehungsweise|und\/oder) /';

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
            $citationKind = $data === null ? null : self::kind($data);
            if ($citationKind === null) {
                // any other markup, or a regulation name on its own: ends the run
                $pending = [];
                $gap = null;
                continue;
            }

            $joined = $gap !== null && $citationKind === $kind
                && preg_match(self::GAP, $gap) && preg_match(self::CONNECTOR, $gap);
            if (!$joined) {
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

    /** "§" or "Art" for a citation, null for an ignored one or a bare regulation name. */
    private static function kind(array $data): ?string
    {
        if (!empty($data['ignore'])) {
            return null;
        }
        if (($data['article'] ?? null) === null
            && !isset($data['compound']) && !isset($data['rangelist']) && !isset($data['articles'])) {
            return null;
        }
        return preg_match('/^(?:die |den )?(?:des )?§/', $data['match']) ? '§' : 'Art';
    }
}
