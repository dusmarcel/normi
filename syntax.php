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
            'Asylverfahrensverordnung', 'AsylverfahrensVO', 'AVVO',
        ],
        'aufnahmerichtlinie' => [
            'Aufnahmerichtlinie', 'AufnahmeRL',
            'Aufnahmerichtlinie 2024', 'AufnahmeRL 2024',
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
            'Eurodac-Verordnung 2024', 'Eurodac-VO 2024',
        ],
        'krisenverordnung' => [
            'Krisenverordnung', 'KrisenVO',
        ],
        'euaa-verordnung' => [
            'EUAA-Verordnung', 'EUAA-VO',
        ],
        'aufnahmerichtlinie-2013' => [
            'Aufnahmerichtlinie 2013', 'AufnahmeRL 2013',
        ],
        'qualifikationsrichtlinie' => [
            'Qualifikationsrichtlinie', 'QualifikationsRL',
            'Anerkennungsrichtlinie', 'AnerkennungsRL',
        ],
        'asylverfahrensrichtlinie' => [
            'Asylverfahrensrichtlinie', 'AsylverfahrensRL',
        ],
        'dublin-iii-verordnung' => [
            'Dublin-III-Verordnung', 'Dublin-III-VO', 'DublinVO',
            'Dublin III', 'Dublin-III',
        ],
        'eurodac-verordnung-2013' => [
            'Eurodac-Verordnung 2013', 'Eurodac-VO 2013',
        ],
        'easo-verordnung' => [
            'EASO-Verordnung', 'EASO-VO',
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
        'aufnahmerichtlinie-2013'         => 'richtlinie_eu_2013_33',
        'qualifikationsrichtlinie'        => 'richtlinie_eu_2011_95',
        'asylverfahrensrichtlinie'        => 'richtlinie_eu_2013_32',
        'dublin-iii-verordnung'           => 'verordnung_eu_nr_604_2013',
        'eurodac-verordnung-2013'         => 'verordnung_eu_nr_603_2013',
        'easo-verordnung'                 => 'verordnung_eu_nr_439_2010',
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
        // Old regulation format: (EU) Nr. NNNN/YYYY
        '604/2013'  => 'dublin-iii-verordnung',
        '603/2013'  => 'eurodac-verordnung-2013',
        '439/2010'  => 'easo-verordnung',
        // Old directive format: YYYY/NN/EU
        '2013/33'   => 'aufnahmerichtlinie-2013',
        '2011/95'   => 'qualifikationsrichtlinie',
        '2013/32'   => 'asylverfahrensrichtlinie',
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
        usort($synonyms, fn($a, $b) => strlen($b) - strlen($a));
        $synonymPattern = implode('|', array_map('preg_quote', $synonyms));
        // Covers: new (EU) YYYY/NNNN, old (EU) Nr. NNNN/YYYY, old directive YYYY/NN/EU
        $euPattern = '(?:(?:Verordnung|Richtlinie) \(EU\) (?:Nr\. )?[0-9]+\/[0-9]+|Richtlinie [0-9]{4}\/[0-9]+\/EU)';

        $subParts = '(?:(?: (?:Absatz|Abs\.) [0-9]+)?(?: (?:Unterabsatz|UA) [0-9]+)?(?: (?:Satz|S\.) [0-9]+)?(?: (?:Nummer|Nr\.) [0-9]+)?(?: lit\. [a-z]\))?)?';

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel) [0-9]+[a-z]?(?:(?:,| und) [0-9]+[a-z]?)+ (?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel) [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subParts . ' (?:' . $synonymPattern . '|' . $euPattern . ')',
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
        if (preg_match('/^((?:Art\.|Artikel) )([0-9]+[a-z]?)((?:(?:,| und) [0-9]+[a-z]?)+) (.+)$/', $match, $m)) {
            preg_match_all('/((?:,| und) )([0-9]+[a-z]?)/', $m[3], $parts, PREG_SET_ORDER);
            $articles = [$m[2]];
            $connectors = [];
            foreach ($parts as $part) {
                $connectors[] = $part[1];
                $articles[] = $part[2];
            }
            return [
                'match'      => $match,
                'articles'   => $articles,
                'connectors' => $connectors,
                'prefix'     => $m[1],
                'reg_text'   => $m[4],
                'article'    => null,
                'regulation' => $this->resolveRegulation($m[4]),
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel) )([0-9]+[a-z]?) bis ([0-9]+[a-z]?) (.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'prefix'     => $m[1],
                'article'    => strtolower($m[2]),
                'article_to' => strtolower($m[3]),
                'reg_text'   => $m[4],
                'regulation' => $this->resolveRegulation($m[4]),
            ];
        }

        if (preg_match('/^(?:Art\.|Artikel) ([0-9]+[a-z]?)(?: f{1,2}\.?| bis [0-9]+[a-z]?)?(?:(?: (?:Absatz|Abs\.) [0-9]+)?(?: (?:Unterabsatz|UA) [0-9]+)?(?: (?:Satz|S\.) [0-9]+)?(?: (?:Nummer|Nr\.) [0-9]+)?(?: lit\. [a-z]\))?)? (.+)$/', $match, $m)) {
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
        // New format: (EU) YYYY/NNNN
        if (preg_match('/^(?:Verordnung|Richtlinie) \(EU\) ([0-9]{4}\/[0-9]+)$/', $term, $eu)) {
            return self::EU_NUMBERS[$eu[1]] ?? null;
        }
        // Old regulation format: (EU) Nr. NNNN/YYYY
        if (preg_match('/^(?:Verordnung|Richtlinie) \(EU\) Nr\. ([0-9]+\/[0-9]{4})$/', $term, $eu)) {
            return self::EU_NUMBERS[$eu[1]] ?? null;
        }
        // Old directive format: YYYY/NN/EU
        if (preg_match('/^Richtlinie ([0-9]{4}\/[0-9]+)\/EU$/', $term, $eu)) {
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

        if (isset($data['article_to'])) {
            $renderer->internallink('art._' . $data['article'] . '_' . $data['regulation'], $data['prefix'] . $data['article']);
            $renderer->doc .= ' bis ';
            $renderer->internallink('art._' . $data['article_to'] . '_' . $data['regulation'], $data['article_to'] . ' ' . $data['reg_text']);
            return true;
        }

        if (isset($data['articles'])) {
            $count = count($data['articles']);
            foreach ($data['articles'] as $i => $article) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                $pageId = 'art._' . strtolower($article) . '_' . $data['regulation'];
                if ($i === 0) {
                    $linkText = $data['prefix'] . $article;
                } elseif ($i === $count - 1) {
                    $linkText = $article . ' ' . $data['reg_text'];
                } else {
                    $linkText = $article;
                }
                $renderer->internallink($pageId, $linkText);
            }
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
