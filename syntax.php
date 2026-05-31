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
    // Slugs of national laws (use § not Artikel — bare Artikel refs on these pages are suppressed)
    const NATIONAL_LAW_SLUGS = ['aufenthaltsgesetz', 'asylgesetz'];

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
            'Dublin III-Verordnung', 'Dublin III-VO',
        ],
        'eurodac-verordnung-2013' => [
            'Eurodac-Verordnung 2013', 'Eurodac-VO 2013',
        ],
        'easo-verordnung' => [
            'EASO-Verordnung', 'EASO-VO',
        ],
        'aufenthaltsgesetz' => [
            'Aufenthaltsgesetzes', 'Aufenthaltsgesetz', 'AufenthG',
        ],
        'asylgesetz' => [
            'Asylgesetzes', 'Asylgesetz', 'AsylG',
        ],
        '__current__' => [
            'vorliegenden Verordnung', 'vorliegenden Richtlinie', 'vorliegenden Gesetzes',
            'vorliegende Verordnung', 'vorliegende Richtlinie',
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
        'aufnahmerichtlinie-2013'         => 'richtlinie_2013_33_eu',
        'qualifikationsrichtlinie'        => 'richtlinie_2011_95_eu',
        'asylverfahrensrichtlinie'        => 'richtlinie_2013_32_eu',
        'dublin-iii-verordnung'           => 'verordnung_eu_nr._604_2013',
        'eurodac-verordnung-2013'         => 'verordnung_eu_nr._603_2013',
        'easo-verordnung'                 => 'verordnung_eu_nr._439_2010',
        'aufenthaltsgesetz'               => 'aufenthaltsgesetz',
        'asylgesetz'                      => 'asylgesetz',
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
        // Covers: new (EU) YYYY/NNNN, old (EU) Nr. NNNN/YYYY, old directive YYYY/NN/EU, old EG directive YYYY/NN/EG
        $euPattern = '(?:(?:Verordnung|Richtlinie) \(EU\) (?:Nr\. )?[0-9]+\/[0-9]+|Richtlinie [0-9]{4}\/[0-9]+\/(?:EU|EG))';

        $absatzNums = '[0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?(?:(?:,| und| oder) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?)?)*)?';
        $subParts = '(?:(?: (?:Absatz|Abs\.|Absätze) ' . $absatzNums . ')?(?: (?:Unterabsatz|UA) [0-9]+)?(?: (?:Satz|S\.) [0-9]+)?(?: (?:Nummer|Nr\.) [0-9]+)?(?: (?:Buchstabe [a-z](?:(?:,| oder) [a-z])*|lit\. [a-z]\)))?)?';

        $nationalSynonyms = [];
        foreach (['aufenthaltsgesetz', 'asylgesetz'] as $slug) {
            $nationalSynonyms = array_merge($nationalSynonyms, self::REGULATIONS[$slug]);
        }
        usort($nationalSynonyms, fn($a, $b) => strlen($b) - strlen($a));
        $nationalPattern = implode('|', array_map('preg_quote', $nationalSynonyms));

        $artPfx  = '(?:der |des |dem |die |den )?';

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?:(?:,| und) [0-9]+[a-z]?)+ ' . $artPfx . '(?:' . $nationalPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subParts . ' ' . $artPfx . '(?:' . $nationalPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|des Artikels) [0-9]+[a-z]?(?:(?:,| und) [0-9]+[a-z]?)+ ' . $artPfx . '(?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|des Artikels) [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subParts . ' ' . $artPfx . '(?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?:,| und) [0-9]+[a-z]?)+',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|des Artikels) [0-9]+[a-z]?' . $subParts,
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?:(?:,| und) [0-9]+[a-z]?)+',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?!(?:,| und) [0-9])' . $subParts,
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Absatz|Abs\.) ' . $absatzNums,
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
        if (preg_match('/^((?:Art\.|Artikel|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?)((?:(?:,| und) [0-9]+[a-z]?)+) (?!und [0-9])(.+)$/', $match, $m)) {
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
                'regulation' => $this->resolveRegulation(preg_replace('/^(?:der|des|dem|die|den) /', '', $m[4])),
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?) bis ([0-9]+[a-z]?) (.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'prefix'     => $m[1],
                'article'    => strtolower($m[2]),
                'article_to' => strtolower($m[3]),
                'reg_text'   => $m[4],
                'regulation' => $this->resolveRegulation(preg_replace('/^(?:der|des|dem|die|den) /', '', $m[4])),
            ];
        }

        if (preg_match('/^(?:Art\.|Artikel|des Artikels|(?:des )?§(?:§)?) ([0-9]+[a-z]?)(?: f{1,2}\.?| bis [0-9]+[a-z]?)?(?:(?: (?:Absatz|Abs\.|Absätze) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?(?:(?:,| und| oder) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?)?)*)?)?(?:(?: (?:Unterabsatz|UA) [0-9]+)?(?: (?:Satz|S\.) [0-9]+)?(?: (?:Nummer|Nr\.) [0-9]+)?(?: (?:Buchstabe [a-z](?:(?:,| oder) [a-z])*|lit\. [a-z]\))?)?))? (?:der |des |dem |die |den )?(?!(?:Absatz|Abs\.|Absätze|Unterabsatz|Satz|S\.|Nummer|Nr\.|Buchstabe|lit\.)\s|bis\s|und\s|oder\s|[0-9])(.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'article'    => strtolower($m[1]),
                'regulation' => $this->resolveRegulation($m[2]),
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?)((?:(?:,| und) [0-9]+[a-z]?)+)$/', $match, $m)) {
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
                'reg_text'   => null,
                'article'    => null,
                'regulation' => '__current__',
            ];
        }

        if (preg_match('/^(?:Art\.|Artikel|des Artikels|(?:des )?§(?:§)?) ([0-9]+[a-z]?)/', $match, $m)) {
            return [
                'match'      => $match,
                'article'    => strtolower($m[1]),
                'regulation' => '__current__',
            ];
        }

        if (preg_match('/^(?:Absatz|Abs\.) [0-9]+/', $match)) {
            return [
                'match'      => $match,
                'article'    => '__current__',
                'regulation' => '__current__',
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
        // Old EG directive format: YYYY/NN/EG (not in our system → always null)
        if (preg_match('/^Richtlinie [0-9]{4}\/[0-9]+\/EG$/', $term)) {
            return null;
        }
        foreach (self::REGULATIONS as $slug => $synonyms) {
            if (in_array($term, $synonyms, true)) {
                return $slug;
            }
        }
        return null;
    }

    /** false = not yet resolved, null = not found, string = slug */
    private $resolvedCurrentRegulation = false;

    /** false = not yet resolved, null = not found, string = article number */
    private $resolvedCurrentArticle = false;

    private function resolveCurrentRegulation(): ?string
    {
        if ($this->resolvedCurrentRegulation !== false) {
            return $this->resolvedCurrentRegulation;
        }
        global $ID;
        $title = $ID ? (p_get_metadata($ID, 'title') ?? '') : '';
        $bestSlug = null;
        $bestLen = 0;
        foreach (self::REGULATIONS as $slug => $synonyms) {
            if ($slug === '__current__') continue;
            foreach ($synonyms as $synonym) {
                $len = mb_strlen($synonym);
                if ($len > $bestLen && mb_stripos($title, $synonym) !== false) {
                    $bestLen = $len;
                    $bestSlug = $slug;
                }
            }
        }
        // Fallback: look for slug in page ID (e.g. "art._60a_aufenthaltsgesetz")
        if ($bestSlug === null && $ID) {
            foreach (self::REGULATIONS as $slug => $synonyms) {
                if ($slug === '__current__') continue;
                if (preg_match('/(?:^|[_:])' . preg_quote($slug, '/') . '(?:$|[_:])/', $ID)) {
                    $bestSlug = $slug;
                    break;
                }
            }
        }
        $this->resolvedCurrentRegulation = $bestSlug;
        return $bestSlug;
    }

    private function resolveCurrentArticle(): ?string
    {
        if ($this->resolvedCurrentArticle !== false) {
            return $this->resolvedCurrentArticle;
        }
        global $ID;
        $title = $ID ? (p_get_metadata($ID, 'title') ?? '') : '';
        // Extract § number from page title, e.g. "§ 62 AufenthG"
        if (preg_match('/\xc2\xa7\s*([0-9]+[a-z]?)/', $title, $m)) {
            $this->resolvedCurrentArticle = strtolower($m[1]);
            return $this->resolvedCurrentArticle;
        }
        // Fallback: extract article number from page ID (e.g. "art._60a_aufenthaltsgesetz")
        if ($ID && preg_match('/^art\._([0-9]+[a-z]?)_/', $ID, $m)) {
            $this->resolvedCurrentArticle = strtolower($m[1]);
            return $this->resolvedCurrentArticle;
        }
        $this->resolvedCurrentArticle = null;
        return null;
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            $renderer->doc .= $data['match'];
            return false;
        }

        $regulation = $data['regulation'];

        if ($regulation === null) {
            $renderer->doc .= hsc($data['match']);
            return true;
        }

        if ($regulation === '__current__') {
            $regulation = $this->resolveCurrentRegulation();
            if ($regulation === null) {
                $renderer->doc .= hsc($data['match']);
                return true;
            }
            // National laws use § not Artikel — bare Artikel refs on these pages are foreign regulations
            if (in_array($regulation, self::NATIONAL_LAW_SLUGS, true)
                && preg_match('/^(?:Art\.|Artikel|Artikeln|des Artikels)/', $data['match'])) {
                $renderer->doc .= hsc($data['match']);
                return true;
            }
        }

        if (isset($data['article_to'])) {
            $renderer->internallink('art._' . $data['article'] . '_' . $regulation, $data['prefix'] . $data['article']);
            $renderer->doc .= ' bis ';
            $renderer->internallink('art._' . $data['article_to'] . '_' . $regulation, $data['article_to'] . ' ' . $data['reg_text']);
            return true;
        }

        if (isset($data['articles'])) {
            $count = count($data['articles']);
            foreach ($data['articles'] as $i => $article) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                $pageId = 'art._' . strtolower($article) . '_' . $regulation;
                if ($i === 0) {
                    $linkText = $data['prefix'] . $article;
                } elseif ($i === $count - 1) {
                    $linkText = $data['reg_text'] !== null ? $article . ' ' . $data['reg_text'] : $article;
                } else {
                    $linkText = $article;
                }
                $renderer->internallink($pageId, $linkText);
            }
            return true;
        }

        $article = $data['article'];
        if ($article === '__current__') {
            $article = $this->resolveCurrentArticle();
            if ($article === null) {
                $renderer->doc .= hsc($data['match']);
                return true;
            }
        }

        if ($article === null) {
            $pageId = self::START_PAGES[$regulation];
        } else {
            $pageId = 'art._' . $article . '_' . $regulation;
        }

        $renderer->internallink($pageId, $data['match']);

        return true;
    }
}
