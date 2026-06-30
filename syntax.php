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
    const NATIONAL_LAW_SLUGS = ['aufenthaltsgesetz', 'asylgesetz', 'beschäftigungsverordnung', 'aufenthaltsverordnung', 'staatsangehörigkeitsgesetz', 'verwaltungsgerichtsordnung', 'freizügigkeitsgesetz-eu'];

    /** Cached synonym/EU patterns for use in handle() (set in connectTo) */
    private $lexerSynonymPattern = '';
    private $lexerEuPattern = '';
    private $lexerSubPartsPattern = '';
    private $lexerNationalPattern = '';

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
            'Asyl- und Migrationsmanagement-Verordnung', 'Asyl- und MigrationsmanagementVO', 'Asyl- und Migrationsmanagement-VO', 'AMM-VO', 'AMMVO',
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
        'rückführungsrichtlinie' => [
            'Rückführungsrichtlinie', 'RückführungsRL',
        ],
        'aeuv' => [
            'Vertrag über die Arbeitsweise der Europäischen Union', 'AEUV',
        ],
        'aufenthaltsgesetz' => [
            'Aufenthaltsgesetzes', 'Aufenthaltsgesetz', 'AufenthG',
        ],
        'asylgesetz' => [
            'Asylgesetzes', 'Asylgesetz', 'AsylG',
        ],
        'beschäftigungsverordnung' => [
            'Beschäftigungsverordnung', 'BeschV',
        ],
        'aufenthaltsverordnung' => [
            'Aufenthaltsverordnung', 'AufenthV',
        ],
        'staatsangehörigkeitsgesetz' => [
            'Staatsangehörigkeitsgesetzes', 'Staatsangehörigkeitsgesetz', 'StAG',
        ],
        'verwaltungsgerichtsordnung' => [
            'Verwaltungsgerichtsordnung', 'VwGO',
        ],
        'freizügigkeitsgesetz-eu' => [
            'Freizügigkeitsgesetz/EU', 'FreizügG/EU',
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
        'rückführungsrichtlinie'          => 'richtlinie_2008_115_eg',
        'aeuv'                            => 'aeuv',
        'aufenthaltsgesetz'               => 'aufenthaltsgesetz',
        'asylgesetz'                      => 'asylgesetz',
        'beschäftigungsverordnung'        => 'beschäftigungsverordnung',
        'aufenthaltsverordnung'           => 'aufenthaltsverordnung',
        'staatsangehörigkeitsgesetz'      => 'staatsangehörigkeitsgesetz',
        'verwaltungsgerichtsordnung'      => 'verwaltungsgerichtsordnung',
        'freizügigkeitsgesetz-eu'         => 'freizügigkeitsgesetz-eu',
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
        // Old EG directive format: YYYY/NN/EG
        '2008/115'  => 'rückführungsrichtlinie',
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
        // Covers: new (EU) YYYY/NNNN, old (EU) Nr. NNNN/YYYY, old directive YYYY/NN/EU or /EG or without suffix,
        // and bare (EU) YYYY/NNNN or (EU) Nr. NNNN/YYYY (e.g. items of a "Verordnungen ... (EU) 2024/1347, ..." list)
        $euPattern = '(?:(?:Verordnung|Richtlinie) \(EU\) (?:Nr\. )?[0-9]+\/[0-9]+|Richtlinie [0-9]{4}\/[0-9]+(?:\/(?:EU|EG))?|\(EU\) (?:Nr\. )?[0-9]+\/[0-9]+)';

        $absatzNums = '[0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?(?:(?:,| und| oder) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?)?)*)?';
        // Optional "erste/zweite Alternative" or "Variante" qualifier after a Buchstabe letter
        $buchstabeQualifier = '(?: (?:erste|zweite|dritte|vierte|fünfte|sechste|siebte|achte|neunte|zehnte) (?:Alternative|Variante))?';
        // (?![a-zäöüß]) ensures the letter is a standalone token, not the start of a following word (e.g. "der")
        $buchstabeLetter = '[a-z](?![a-zäöüß])' . $buchstabeQualifier;
        $buchstaben = '(?:(?:Buchstabe|Buchstaben|Buchst\.) ' . $buchstabeLetter
            . '(?:(?:,| und| oder)? (?:(?:Buchstabe|Buchstaben|Buchst\.) )?' . $buchstabeLetter . ')*'
            . '|lit\. [a-z]\))';
        $extSubParts   = '(?: (?:Unterabsatz|Unterabsätze|UA) ' . $absatzNums . ')?(?: (?:Satz|S\.) [0-9]+)?(?: (?:Nummer|Nr\.) [0-9]+)?(?:,? ' . $buchstaben . ')?';
        $subPartsInner = '(?: (?:Absatz|Abs\.|Absätze) ' . $absatzNums . '(?:, (?:Unterabsatz|Unterabsätze|UA) ' . $absatzNums . ')?)?'
            . '(?: (?:Unterabsatz|Unterabsätze|UA) ' . $absatzNums . ')?'
            . '(?: (?:Satz|S\.) [0-9]+)?'
            . '(?: (?:Nummer|Nr\.) [0-9]+)?'
            . '(?:,? ' . $buchstaben . ')?'
            . '(?: und (?:(?:Absatz|Abs\.) ' . $absatzNums . ')' . $extSubParts . ')*';
        $subParts = '(?:' . $subPartsInner . ')?';

        $nationalSynonyms = [];
        foreach (self::NATIONAL_LAW_SLUGS as $slug) {
            $nationalSynonyms = array_merge($nationalSynonyms, self::REGULATIONS[$slug]);
        }
        usort($nationalSynonyms, fn($a, $b) => strlen($b) - strlen($a));
        $nationalPattern = implode('|', array_map('preg_quote', $nationalSynonyms));

        $artPfx  = '(?:der |des |dem |die |den )?';

        $this->lexerSynonymPattern  = $synonymPattern;
        $this->lexerEuPattern       = $euPattern;
        $this->lexerSubPartsPattern = $subPartsInner;
        $this->lexerNationalPattern = $nationalPattern;

        // Compound: "Artikel 3, Artikel 4 Absatz 1, ..., die Artikel 16 bis 18 der Richtlinie 2008/115/EG"
        $singleItemPat = '(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?' . $subPartsInner . ')?';
        $compoundSep   = '(?:, (?:und (?:die |den )?)?| und (?:die |den )?)(?=(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels))';
        $this->Lexer->addSpecialPattern(
            '(?:' . $singleItemPat . $compoundSep . ')++' . $singleItemPat . ' ' . $artPfx . '(?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?:(?:,| und| oder) [0-9]+[a-z]?)+ ' . $artPfx . '(?:' . $nationalPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subParts . ' ' . $artPfx . '(?:' . $nationalPattern . ')',
            $mode,
            'plugin_normi'
        );

        // Compound §-list without repeated "§": "§§ 2 Abs. 3, 5 Abs. 1 Nr. 1 AufenthG"
        // (each item may carry its own Absatz/Unterabsatz/Satz/Nr./Buchstabe; not possessive so the
        // greedy Absatz-number list inside subPartsInner can backtrack to find the correct item split)
        $sectionItemPat = '(?:(?:des )?§(?:§)? )?[0-9]+[a-z]?(?:' . $subPartsInner . ')?';
        $sectionSep     = '(?:,| und| oder) ';
        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? (?:' . $sectionItemPat . $sectionSep . ')+' . $sectionItemPat . ' ' . $artPfx . '(?:' . $nationalPattern . ')',
            $mode,
            'plugin_normi'
        );

        // Also handles "Artikeln 1 und 79 Absatz 3 der Verordnung (EU) 2024/1348": the trailing
        // $subParts (optional) lets the LAST item carry Absatz/Unterabsatz/… before the regulation.
        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?:,| und| oder) [0-9]+[a-z]?)+' . $subParts . ' ' . $artPfx . '(?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subParts . ' ' . $artPfx . '(?:' . $synonymPattern . '|' . $euPattern . ')',
            $mode,
            'plugin_normi'
        );

        // Bare "Artikel 25 bis 28 und 34" / "Artikel 25 bis 28" (no explicit law — falls back to the current page's regulation)
        $artBisListInner = '[0-9]+[a-z]?(?: bis [0-9]+[a-z]?)(?:(?:,| und| oder) [0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?)*';
        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|Artikeln|des Artikels) ' . $artBisListInner,
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?:,| und| oder) [0-9]+[a-z]?)+',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            // Possessive [0-9]++[a-z]?+ so the trailing lookaheads can't be satisfied by backtracking
            // into a shorter article number (e.g. matching just "2" out of "25 bis 28")
            '(?:Art\.|Artikel|des Artikels) [0-9]++[a-z]?+(?:' . $subPartsInner . ')?+(?!, [0-9])(?! bis [0-9])',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?:(?:,| und| oder) [0-9]+[a-z]?)+',
            $mode,
            'plugin_normi'
        );

        $this->Lexer->addSpecialPattern(
            '(?:des )?§(?:§)? [0-9]+[a-z]?(?!(?:,| und| oder) [0-9])' . $subParts,
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
        // Compound: "Artikel 3, Artikel 4 Absatz 1, ..., die Artikel 16 bis 18 der Richtlinie"
        // Also handles: "Artikeln 1 und 79 Absatz 3 der Verordnung (EU) 2024/1348"
        // (subsequent items are bare digits without a repeated "Artikel" prefix)
        if (!empty($this->lexerSynonymPattern)) {
            $regPfxPat = '(?:der |des |dem |die |den )?';
            if (preg_match('~ ' . $regPfxPat . '(' . $this->lexerSynonymPattern . '|' . $this->lexerEuPattern . ')$~', $match, $rm)) {
                $regSlug = $this->resolveRegulation($rm[1]);
                if ($regSlug !== null) {
                    $regSuffix = $rm[0];
                    $itemsText = substr($match, 0, -strlen($regSuffix));
                    // If itemsText is actually just ONE Artikel item with a full subParts tail
                    // (e.g. "Artikel 5 Absatz 1 und 3"), it's not a compound at all — the "und 3"
                    // belongs to the Absatz-list, not a second Artikel. Skip and let the single-item
                    // branch below handle it as one cohesive reference.
                    $singleItemFullPat = '/^(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?' . $this->lexerSubPartsPattern . ')?$/';
                    if (!preg_match($singleItemFullPat, $itemsText)) {
                        // Prefer splitting on "Artikel"-introducing separators (compound with repeated keyword);
                        // fall back to bare-digit lookahead for "Artikeln N und M Absatz K" constructs.
                        $sepPat = '/(?:, (?:und (?:die |den )?)?| und (?:die |den )?)(?=(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels))/';
                        $items = preg_split($sepPat, $itemsText);
                        if (count($items) < 2) {
                            $sepPat = '/(?:,| und| oder) (?=[0-9])/';
                            $items = preg_split($sepPat, $itemsText);
                        }
                        if (count($items) >= 2) {
                            preg_match_all($sepPat, $itemsText, $connMatches);
                            $parsedItems = [];
                            foreach ($items as $idx => $itemText) {
                                $displayText = ($idx === count($items) - 1) ? $itemText . $regSuffix : $itemText;
                                if (preg_match('/(?:Art\.|Artikel|Artikeln|des Artikels) ([0-9]+[a-z]?) bis ([0-9]+[a-z]?)/', $itemText, $bm)) {
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($bm[1]), 'article_to' => strtolower($bm[2])];
                                } elseif (preg_match('/(?:Art\.|Artikel|Artikeln|des Artikels) ([0-9]+[a-z]?)/', $itemText, $nm)) {
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($nm[1]), 'article_to' => null];
                                } elseif (preg_match('/^([0-9]+[a-z]?)/', $itemText, $nm)) {
                                    // Bare-digit item (no repeated "Artikel" prefix, e.g. "79 Absatz 3")
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($nm[1]), 'article_to' => null];
                                }
                            }
                            if (count($parsedItems) >= 2) {
                                return ['match' => $match, 'compound' => $parsedItems, 'connectors' => $connMatches[0], 'regulation' => $regSlug];
                            }
                        }
                    }
                }
            }
        }

        // Compound §-list without repeated "§": "§§ 2 Abs. 3, 5 Abs. 1 Nr. 1 AufenthG"
        if (!empty($this->lexerNationalPattern)) {
            $regPfxPat = '(?:der |des |dem |die |den )?';
            if (preg_match('~ ' . $regPfxPat . '(' . $this->lexerNationalPattern . ')$~', $match, $rm)) {
                $regSlug = $this->resolveRegulation($rm[1]);
                if ($regSlug !== null) {
                    $regSuffix = $rm[0];
                    $itemsText = substr($match, 0, -strlen($regSuffix));
                    // If itemsText is actually just ONE § item with a full subParts tail
                    // (e.g. "§ 60 Absatz 5 und 7"), it's not a compound at all — the "und 7"
                    // belongs to the Absatz-list, not a second §. Skip and let the single-item
                    // branch below handle it as one cohesive reference.
                    $singleItemFullPat = '/^(?:(?:des )?§(?:§)? )?[0-9]+[a-z]?(?:' . $this->lexerSubPartsPattern . ')?$/';
                    if (!preg_match($singleItemFullPat, $itemsText)) {
                        $sepPat = '/(?:,| und| oder) (?=[0-9])/';
                        $items = preg_split($sepPat, $itemsText);
                        if (count($items) >= 2) {
                            preg_match_all($sepPat, $itemsText, $connMatches);
                            $parsedItems = [];
                            foreach ($items as $idx => $itemText) {
                                $displayText = ($idx === count($items) - 1) ? $itemText . $regSuffix : $itemText;
                                if (preg_match('/^(?:(?:des )?§(?:§)? )?([0-9]+[a-z]?)/', $itemText, $nm)) {
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($nm[1]), 'article_to' => null];
                                }
                            }
                            if (count($parsedItems) >= 2) {
                                return ['match' => $match, 'compound' => $parsedItems, 'connectors' => $connMatches[0], 'regulation' => $regSlug];
                            }
                        }
                    }
                }
            }
        }

        // Bare "Artikel 25 bis 28 und 34" (no explicit law) — resolves via the current page's regulation
        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels) )([0-9]+[a-z]?(?: bis [0-9]+[a-z]?)(?:(?:,| und| oder) [0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?)*)$/', $match, $m)) {
            preg_match_all('/(?:^|((?:,| und| oder) ))([0-9]+[a-z]?)(?: bis ([0-9]+[a-z]?))?/', $m[2], $am, PREG_SET_ORDER);
            $items = [];
            $connectors = [];
            foreach ($am as $i => $pm) {
                if ($i > 0) {
                    $connectors[] = $pm[1];
                }
                $items[] = ['article' => strtolower($pm[2]), 'article_to' => (isset($pm[3]) && $pm[3] !== '') ? strtolower($pm[3]) : null];
            }
            return [
                'match'      => $match,
                'rangelist'  => $items,
                'connectors' => $connectors,
                'prefix'     => $m[1],
                'regulation' => '__current__',
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?)((?:(?:,| und| oder) [0-9]+[a-z]?)+) (?!(?:und|oder) [0-9])(.+)$/', $match, $m)) {
            preg_match_all('/((?:,| und| oder) )([0-9]+[a-z]?)/', $m[3], $parts, PREG_SET_ORDER);
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

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?) bis ([0-9]+[a-z]?) (.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'prefix'     => $m[1],
                'article'    => strtolower($m[2]),
                'article_to' => strtolower($m[3]),
                'reg_text'   => $m[4],
                'regulation' => $this->resolveRegulation(preg_replace('/^(?:der|des|dem|die|den) /', '', $m[4])),
            ];
        }

        if (preg_match('/^(?:Art\.|Artikel|des Artikels|(?:des )?§(?:§)?) ([0-9]+[a-z]?)(?: f{1,2}\.?| bis [0-9]+[a-z]?)?(?:' . $this->lexerSubPartsPattern . ')? (?:der |des |dem |die |den )?(?!(?:Absatz|Abs\.|Absätze|Unterabsatz|Unterabsätze|Satz|S\.|Nummer|Nr\.|Buchstabe|Buchstaben|lit\.)\s|bis\s|und\s|oder\s|[0-9])(.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'article'    => strtolower($m[1]),
                'regulation' => $this->resolveRegulation($m[2]),
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?)((?:(?:,| und| oder) [0-9]+[a-z]?)+)$/', $match, $m)) {
            preg_match_all('/((?:,| und| oder) )([0-9]+[a-z]?)/', $m[3], $parts, PREG_SET_ORDER);
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
        // Bare (EU) YYYY/NNNN or (EU) Nr. NNNN/YYYY without a "Verordnung"/"Richtlinie" prefix
        // (e.g. items of a "Verordnungen ... 1. (EU) 2024/1347, 2. (EU) 2024/1348, ..." list)
        if (preg_match('/^\(EU\) (?:Nr\. ([0-9]+\/[0-9]{4})|([0-9]{4}\/[0-9]+))$/', $term, $eu)) {
            $number = $eu[1] !== '' ? $eu[1] : $eu[2];
            return self::EU_NUMBERS[$number] ?? null;
        }
        // Old directive format: YYYY/NN/EU
        if (preg_match('/^Richtlinie ([0-9]{4}\/[0-9]+)\/EU$/', $term, $eu)) {
            return self::EU_NUMBERS[$eu[1]] ?? null;
        }
        // Old EG directive format: YYYY/NN/EG
        if (preg_match('/^Richtlinie ([0-9]{4}\/[0-9]+)\/EG$/', $term, $eu)) {
            return self::EU_NUMBERS[$eu[1]] ?? null;
        }
        // Directive cited without suffix: YYYY/NN
        if (preg_match('/^Richtlinie ([0-9]{4}\/[0-9]+)$/', $term, $eu)) {
            return self::EU_NUMBERS[$eu[1]] ?? null;
        }
        foreach (self::REGULATIONS as $slug => $synonyms) {
            if (in_array($term, $synonyms, true)) {
                return $slug;
            }
        }
        return null;
    }

    /**
     * Infers the regulation for a bare reference from the table column it
     * appears in: if we're past the first row of a table, look up the
     * first-row cell of the same column and try to resolve it as a regulation.
     */
    private function resolveTableColumnRegulation(Doku_Renderer $renderer): ?string
    {
        $doc = $renderer->doc;

        $tablePos = strrpos($doc, '<table');
        if ($tablePos === false || strpos($doc, '</table>', $tablePos) !== false) {
            return null;
        }

        if (!preg_match_all('/<tr\b[^>]*>(.*?)(?:<\/tr>|$)/s', substr($doc, $tablePos), $rowMatches)) {
            return null;
        }
        $rows = $rowMatches[1];
        if (count($rows) < 2) {
            return null;
        }

        $currentRow = $rows[count($rows) - 1];
        preg_match_all('/<t[hd]\b[^>]*>/', $currentRow, $cellOpens);
        $columnIndex = count($cellOpens[0]) - 1;
        if ($columnIndex < 0) {
            return null;
        }

        preg_match_all('/<t[hd]\b[^>]*>(.*?)<\/t[hd]>/s', $rows[0], $headerCells);
        if (!isset($headerCells[1][$columnIndex])) {
            return null;
        }

        $headerText = trim(html_entity_decode(strip_tags($headerCells[1][$columnIndex]), ENT_QUOTES, 'UTF-8'));
        if ($headerText === '') {
            return null;
        }

        $slug = $this->resolveRegulation($headerText);
        return $slug === '__current__' ? null : $slug;
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
            // In a table, an unspecified regulation may be implied by the first-row
            // header of the same column (e.g. "^ Richtlinie 2013/33/EU ^ ... ^").
            $tableRegulation = $this->resolveTableColumnRegulation($renderer);
            if ($tableRegulation !== null) {
                $regulation = $tableRegulation;
            } else {
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
        }

        if (isset($data['compound'])) {
            foreach ($data['compound'] as $i => $item) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                if ($item['article_to'] !== null
                    && preg_match('/^((?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?) bis (.+)$/', $item['text'], $bm)) {
                    $renderer->internallink('art._' . $item['article'] . '_' . $regulation, $bm[1]);
                    $renderer->doc .= ' bis ';
                    $renderer->internallink('art._' . $item['article_to'] . '_' . $regulation, $bm[2]);
                } else {
                    $renderer->internallink('art._' . $item['article'] . '_' . $regulation, $item['text']);
                }
            }
            return true;
        }

        if (isset($data['rangelist'])) {
            foreach ($data['rangelist'] as $i => $item) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                $fromText = $i === 0 ? $data['prefix'] . $item['article'] : $item['article'];
                $renderer->internallink('art._' . $item['article'] . '_' . $regulation, $fromText);
                if ($item['article_to'] !== null) {
                    $renderer->doc .= ' bis ';
                    $renderer->internallink('art._' . $item['article_to'] . '_' . $regulation, $item['article_to']);
                }
            }
            return true;
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
