<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * DokuWiki Plugin normi (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Marcel Keienborg <marcel@aufentha.lt>
 */
class syntax_plugin_normi extends SyntaxPlugin
{
    // Canonical page slug => list of text synonyms
    const REGULATIONS = [
        'anerkennungsverordnung' => [
            'Anerkennungsverordnung', 'Qualifikationsverordnung',
            'AnerkennungsVO', 'QualifikationsVO', 'QVO',
            'Statusverordnung', 'StatusVO',
        ],
        'asylverfahrensverordnung' => [
            'Asylverfahrensverordnung', 'AsylverfahrensVO',
        ],
        'aufnahmerichtlinie' => [
            'Aufnahmerichtlinie', 'AufnahmeRL',
        ],
        'grenzrückführungsverordnung' => [
            'Grenzrückführungsverordnung', 'GrenzrückführungsVO',
            'GrenzRüFüVO', 'GrenzRFVO',
        ],
        'resettlementverordnung' => [
            'Resettlementverordnung', 'ResettlementVO',
        ],
        'ammvo' => [
            'Asyl- und Migrationsmanagement-Verordnung', 'AMM-VO', 'AMMVO',
        ],
        'screening-konsistenz-verordnung' => [
            'Screening-Konsistenz-Verordnung', 'Screening-Konsistenz-VO',
        ],
        'screening-verordnung' => [
            'Screening-Verordnung', 'Screening-VO',
        ],
        'eurodac-verordnung' => [
            'Eurodac-Verordnung', 'Eurodac-VO',
        ],
        'krisenverordnung' => [
            'Krisenverordnung', 'KrisenVO',
        ],
        'euaa-verordnung' => [
            'EUAA-Verordnung', 'EUAA-VO',
        ],
    ];

    // Canonical page slug => start page
    const START_PAGES = [
        'anerkennungsverordnung'          => 'verordnung_eu_2024_1347',
        'asylverfahrensverordnung'        => 'verordnung_eu_2024_1348',
        'aufnahmerichtlinie'              => 'richtlinie_eu_2024_1346',
        'grenzrückführungsverordnung'     => 'verordnung_eu_2024_1349',
        'resettlementverordnung'          => 'verordnung_eu_2024_1350',
        'ammvo'                           => 'verordnung_eu_2024_1351',
        'screening-konsistenz-verordnung' => 'verordnung_eu_2024_1352',
        'screening-verordnung'            => 'verordnung_eu_2024_1356',
        'eurodac-verordnung'              => 'verordnung_eu_2024_1358',
        'krisenverordnung'                => 'verordnung_eu_2024_1359',
        'euaa-verordnung'                 => 'verordnung_eu_2021_2303',
    ];

    // EU regulation/directive number => canonical page slug
    const EU_NUMBERS = [
        '2024/1346' => 'aufnahmerichtlinie',
        '2024/1347' => 'anerkennungsverordnung',
        '2024/1348' => 'asylverfahrensverordnung',
        '2024/1349' => 'grenzrückführungsverordnung',
        '2024/1350' => 'resettlementverordnung',
        '2024/1351' => 'ammvo',
        '2024/1352' => 'screening-konsistenz-verordnung',
        '2024/1356' => 'screening-verordnung',
        '2024/1358' => 'eurodac-verordnung',
        '2024/1359' => 'krisenverordnung',
        '2021/2303' => 'euaa-verordnung',
    ];

    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 1;
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $synonyms = [];
        foreach (self::REGULATIONS as $terms) {
            $synonyms = array_merge($synonyms, $terms);
        }
        $synonymPattern = implode('|', array_map('preg_quote', $synonyms));
        $euPattern      = '(?:Verordnung|Richtlinie) \(EU\) [0-9]{4}\/[0-9]+';

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel) [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)? (?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        if (preg_match('/^(?:Art\.|Artikel) ([0-9]+[a-z]?)(?: f{1,2}\.?| bis [0-9]+[a-z]?)? (.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'article'    => strtolower($m[1]),
                'regulation' => $this->resolveRegulation($m[2]),
            ];
        }

        return [
            'match'      => $match,
            'article'    => null,
            'regulation' => $this->resolveRegulation($match),
        ];
    }

    private function resolveRegulation(string $term): ?string
    {
        if (preg_match('/^(?:Verordnung|Richtlinie) \(EU\) ([0-9]{4}\/[0-9]+)$/', $term, $eu)) {
            return self::EU_NUMBERS[$eu[1]] ?? null;
        }
        foreach (self::REGULATIONS as $slug => $synonyms) {
            if (in_array($term, $synonyms, true)) {
                return $slug;
            }
        }
        return null;
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            $renderer->doc .= $data['match'];
            return false;
        }

        if ($data['regulation'] === null) {
            $renderer->doc .= hsc($data['match']);
            return true;
        }

        if ($data['article'] === null) {
            $pageId = self::START_PAGES[$data['regulation']];
        } else {
            $pageId = 'art._' . $data['article'] . '_' . $data['regulation'];
        }

        $renderer->internallink($pageId, $data['match']);

        return true;
    }
}
