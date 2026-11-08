<?php

use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\plugin\normi\ExternalLaws;

/**
 * DokuWiki Plugin normi (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Marcel Keienborg <marcel@aufentha.lt>
 */
class syntax_plugin_normi extends SyntaxPlugin
{
    // Slugs of national laws (use § not Artikel — bare Artikel refs on these pages are suppressed)
    const NATIONAL_LAW_SLUGS = ['aufenthaltsgesetz', 'asylgesetz', 'asylbewerberleistungsgesetz', 'beschäftigungsverordnung', 'aufenthaltsverordnung', 'staatsangehörigkeitsgesetz', 'verwaltungsgerichtsordnung', 'freizügigkeitsgesetz-eu', 'ukraine-aufenthalts-übergangsverordnung'];

    /** Bare item list after "§"/"Artikel": a range ("25 bis 28") or at least two items, each possibly a range */
    const BARE_LIST = '[0-9]+[a-z]?(?: bis [0-9]+[a-z]?(?:(?:,| und| oder| sowie) [0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?)*|(?:(?:,| und| oder| sowie) [0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?)+)';

    /** Cached synonym/EU patterns for use in handle() (set in connectTo) */
    private $lexerSynonymPattern = '';
    private $lexerEuPattern = '';
    private $lexerExtArticlePattern = '';
    private $lexerSubPartsPattern = '';
    private $lexerNationalPattern = '';
    private $lexerSectionItemPattern = '';
    private $lexerSectionSepPattern = '';

    // Canonical page slug => list of text synonyms
    const REGULATIONS = [
        'anerkennungsverordnung' => [
            'Anerkennungsverordnung', 'Qualifikationsverordnung',
            'AnerkennungsVO', 'QualifikationsVO', 'QVO',
            'Statusverordnung', 'StatusVO',
        ],
        'asylverfahrensverordnung' => [
            'Asylverfahrensverordnung', 'AsylverfahrensVO', 'AVVO', 'AsylVfVO',
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
        'rückführungsverordnung' => [
            'Rückführungsverordnung', 'RückführungsVO',
        ],
        'aufnahmerichtlinie-2013' => [
            'Aufnahmerichtlinie 2013', 'AufnahmeRL 2013',
        ],
        'qualifikationsrichtlinie' => [
            'Qualifikationsrichtlinie', 'QualifikationsRL',
            'Anerkennungsrichtlinie', 'AnerkennungsRL', 'QRL',
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
        'arb-1-80' => [
            'Assoziationsratsbeschluss EWG – Türkei Nr. 1/80', 'Assoziationsratsbeschlusses EWG – Türkei Nr. 1/80',
            'Assoziationsratsbeschluss 1/80', 'Assoziationsratsbeschlusses 1/80',
            'ARB 1/80',
        ],
        'aeuv' => [
            'Vertrag über die Arbeitsweise der Europäischen Union',
            'Vertrags über die Arbeitsweise der Europäischen Union',
            'AEUV',
        ],
        'aufenthaltsgesetz' => [
            'Aufenthaltsgesetzes', 'Aufenthaltsgesetz', 'AufenthG',
        ],
        'asylgesetz' => [
            'Asylgesetzes', 'Asylgesetz', 'AsylG',
        ],
        'asylbewerberleistungsgesetz' => [
            'Asylbewerberleistungsgesetzes', 'Asylbewerberleistungsgesetz', 'AsylbLG',
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
        'ukraine-aufenthalts-übergangsverordnung' => [
            'Ukraine-Aufenthalts-Übergangsverordnung', 'UkraineAufenthÜV',
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
        'rückführungsverordnung'          => 'verordnung_eu_2026_2280',
        'aufnahmerichtlinie-2013'         => 'richtlinie_2013_33_eu',
        'qualifikationsrichtlinie'        => 'richtlinie_2011_95_eu',
        'asylverfahrensrichtlinie'        => 'richtlinie_2013_32_eu',
        'dublin-iii-verordnung'           => 'verordnung_eu_nr._604_2013',
        'eurodac-verordnung-2013'         => 'verordnung_eu_nr._603_2013',
        'easo-verordnung'                 => 'verordnung_eu_nr._439_2010',
        'rückführungsrichtlinie'          => 'richtlinie_2008_115_eg',
        'arb-1-80'                        => 'arb-1-80',
        'aeuv'                            => 'aeuv',
        'aufenthaltsgesetz'               => 'aufenthaltsgesetz',
        'asylgesetz'                      => 'asylgesetz',
        'asylbewerberleistungsgesetz'     => 'asylbewerberleistungsgesetz',
        'beschäftigungsverordnung'        => 'beschäftigungsverordnung',
        'aufenthaltsverordnung'           => 'aufenthaltsverordnung',
        'staatsangehörigkeitsgesetz'      => 'staatsangehörigkeitsgesetz',
        'verwaltungsgerichtsordnung'      => 'verwaltungsgerichtsordnung',
        'freizügigkeitsgesetz-eu'         => 'freizügigkeitsgesetz-eu',
        'ukraine-aufenthalts-übergangsverordnung' => 'ukraine-aufenthalts-übergangsverordnung',
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
        '2026/2280' => 'rückführungsverordnung',
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

    /**
     * Builds a regex alternation of the given literal terms with common prefixes factored out
     * ("Asylverfahrens(?:verordnung|VO|richtlinie|RL)" instead of listing each term in full).
     * The synonym lists are embedded in several Lexer patterns, so this keeps the combined master
     * regex well under PCRE's compiled-size limit. Siblings always differ in their first character,
     * so branch order is irrelevant; a term that is a prefix of another becomes a greedy optional
     * tail, which preserves the previous longest-synonym-first preference.
     */
    private static function buildTrieAlternation(array $terms): string
    {
        $trie = [];
        foreach ($terms as $term) {
            $node = &$trie;
            foreach (mb_str_split($term) as $ch) {
                $node[$ch] ??= [];
                $node = &$node[$ch];
            }
            $node[''] = true;
            unset($node);
        }
        return self::trieToRegex($trie);
    }

    private static function trieToRegex(array $node): string
    {
        $isEnd = isset($node['']);
        unset($node['']);
        $branches = [];
        foreach ($node as $ch => $child) {
            $branches[] = preg_quote((string) $ch) . self::trieToRegex($child);
        }
        if (!$branches) {
            return '';
        }
        $alt = count($branches) > 1 ? '(?:' . implode('|', $branches) . ')' : $branches[0];
        if (!$isEnd) {
            return $alt;
        }
        return (count($branches) > 1 ? $alt : '(?:' . $alt . ')') . '?';
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $synonyms = [];
        foreach (self::REGULATIONS as $terms) {
            $synonyms = array_merge($synonyms, $terms);
        }
        $synonymPattern = self::buildTrieAlternation($synonyms);
        // Covers: new (EU) YYYY/NNNN, old (EU) Nr. NNNN/YYYY, old directive YYYY/NN/EU or /EG or without suffix,
        // and bare (EU) YYYY/NNNN or (EU) Nr. NNNN/YYYY (e.g. items of a "Verordnungen ... (EU) 2024/1347, ..." list)
        $euPattern = '(?:(?:Verordnung|Richtlinie) \(EU\) (?:Nr\. )?[0-9]+\/[0-9]+|Richtlinie [0-9]{4}\/[0-9]+(?:\/(?:EU|EG))?|\(EU\) (?:Nr\. )?[0-9]+\/[0-9]+)';

        // Absatz/Unterabsatz value list: "1", "1 bis 3", "1, 2 und 4", "2 bis 4 oder 6". Kept as
        // short as possible — it occurs about 45 times in the Lexer's combined regex (PCRE size limit).
        $absatzNums = '[0-9]+[a-z]?(?:(?: bis|,| und| oder| sowie) [0-9]+[a-z]?)*';
        // Optional "erste/zweite Alternative" or "Variante" qualifier on a Buchstabe reference.
        // Placed once at the END of the buchstaben group (not per-letter) to avoid duplicating
        // ~90 chars of PCRE per letter occurrence and stay within PCRE's compiled-size limit.
        // Any ordinal ("erste", "zweite", …, "letzte") — spelled generically to keep it short.
        $buchstabeQualifier = '(?: (?:[a-z]+|fünf)te (?:Alternative|Variante))?';
        // (?![a-zäöüß]) ensures the letter is a standalone token, not the start of a word (e.g. "der")
        $buchstabeLetter = '[a-z](?![a-zäöüß])';
        $buchstaben = '(?:Buchst(?:aben?|\.) ' . $buchstabeLetter
            . '(?:(?: bis ' . $buchstabeLetter . ')?(?:(?:,| und| oder| sowie)? (?:Buchst(?:aben?|\.) )?' . $buchstabeLetter . ')*)'
            . $buchstabeQualifier
            . '|lit\. [a-z]\)|[a-z]\))';
        // Simpler buchstaben variant for $extSubParts — keeps the repeated-group pattern compact to
        // avoid pushing the combined Lexer master regex over PCRE's internal compiled-size limit.
        $buchstabenSimple = '(?:Buchst(?:aben?|\.) ' . $buchstabeLetter
            . '(?:(?:,| und| oder| sowie)? (?:Buchst(?:aben?|\.) )?' . $buchstabeLetter . ')*'
            . $buchstabeQualifier
            . '|lit\. [a-z]\))';
        $extSubParts   = '(?: (?:Unterabsatz|Unterabsätze|UA) ' . $absatzNums . ')?(?: (?:Satz|S\.) [0-9]+)?(?: (?:Nummer|Nr\.) [0-9]+[a-z]?)?(?:,? ' . $buchstabenSimple . ')?';
        // $withNummerQualifier adds ~104 chars per call site (the qualifier regex itself is long);
        // $withSatzList/$withNummerList ~26 each — all default off and are opted into only where a
        // sentence actually needs them (currently just the compound §-list item pattern below),
        // since $buildSubParts' output is reused several times across the Lexer's patterns and
        // that cost multiplies with each reuse (PCRE size limit).
        $buildSubParts = fn(string $nummer, string $connector, bool $withNummerQualifier = false, bool $withSatzList = false, bool $withNummerList = false) => '(?: (?:Absatz|Abs\.|Absätze) ' . $absatzNums . '(?:, (?:Unterabsatz|Unterabsätze|UA) ' . $absatzNums . ')?)?'
            . '(?: (?:Unterabsatz|Unterabsätze|UA) ' . $absatzNums . ')?'
            // "Satz 6 und 7" — without this, a bare second Satz number is mistaken for the start
            // of the next item in an outer compound list (e.g. linked to the wrong § entirely).
            . '(?: (?:Satz|S\.) [0-9]+' . ($withSatzList ? '(?:(?: und| oder) [0-9]+)*' : '') . ')?'
            . '(?: (?:Alternative|Alt\.) [0-9]+)?'
            // The "erste/zweite Alternative"/"Variante" qualifier may directly follow a Nummer
            // with no Buchstabe in between (e.g. "Nummer 2 erste Alternative"), not just a Buchstabe.
            // $withNummerList additionally covers "Nummer 4 und 8" for the same reason as Satz above.
            . '(?: (?:Nummer|Nr\.) ' . $nummer . ($withNummerList ? '(?:(?: und| oder) [0-9]+[a-z]?)*' : '') . ')?' . ($withNummerQualifier ? $buchstabeQualifier : '')
            // Satz may also follow the Nummer: "§ 99 Absatz 1 Nummer 13a Satz 1 des Aufenthaltsgesetzes"
            . '(?: (?:Satz|S\.) [0-9]+)?'
            . '(?:,? ' . $buchstaben . ')?'
            // Bare comma is also accepted here (not just $connector) because the keyword
            // "Absatz|Abs." immediately follows, which disambiguates it from a comma that
            // separates the next item in an outer §-list (e.g. "§ 98 Absatz 2 Nummer 2,
            // Absatz 2a, Absatz 4 und 5" — each further Absatz clause for the same § 98).
            // Unlike the word connectors, a comma has no leading space of its own (it attaches
            // directly to the preceding token: "...Nummer 2, Absatz 2a", not "...Nummer 2 , Absatz 2a").
            . '(?:(?:(?: ' . $connector . ')|,) (?:(?:Absatz|Abs\.) ' . $absatzNums . ')' . $extSubParts . ')*';
        $subPartsInner = $buildSubParts('[0-9]+[a-z]?', '(?:und|oder|sowie)');
        // Variant used only by the single-item patterns, with a Nummer list ("Nummer 1, 3 und 4",
        // "Nr. 1 bis 3") and "in Verbindung mit"/"i. V. m." before a further Absatz ("§ 81 Absatz 5 in
        // Verbindung mit Absatz 3 oder Absatz 4"): in the compound patterns subPartsInner occurs
        // repeatedly, and these there push the combined Lexer master regex over PCRE's compiled-size limit
        $subPartsListInner = $buildSubParts('[0-9]+[a-z]?(?:(?:,| und| oder| sowie| bis) [0-9]+[a-z]?)*', '(?:und|oder|sowie|in Verbindung mit|i\. ?V\. ?m\.)');
        $subParts     = '(?:' . $subPartsInner . ')?';
        $subPartsList = '(?:' . $subPartsListInner . ')?';

        $nationalSynonyms = [];
        foreach (self::NATIONAL_LAW_SLUGS as $slug) {
            $nationalSynonyms = array_merge($nationalSynonyms, self::REGULATIONS[$slug]);
        }
        // Laws without pages in this wiki (linked to dejure.org/buzer.de/gesetze-im-internet.de):
        // known long forms plus any abbreviation-shaped token. They follow the wiki's own laws in
        // each alternation, so a known synonym always wins. Byte-mode end guard (no /u in the Lexer).
        $extParagraphPattern = ExternalLaws::lexerPattern(false);
        $extArticlePattern   = ExternalLaws::lexerPattern(true);
        $nationalPattern = self::buildTrieAlternation($nationalSynonyms) . '|' . $extParagraphPattern;
        // Regulation slot of the Artikel patterns (the standalone-name pattern below keeps $synonymPattern|$euPattern)
        $artLawPattern = $synonymPattern . '|' . $euPattern . '|' . $extArticlePattern;

        $artPfx  = '(?:der |des |dem |die |den )?';

        $this->lexerSynonymPattern  = $synonymPattern;
        $this->lexerEuPattern       = $euPattern;
        $this->lexerExtArticlePattern = $extArticlePattern;
        $this->lexerSubPartsPattern = $subPartsListInner;
        $this->lexerNationalPattern = $nationalPattern;

        // DokuWiki joins the patterns of ALL modes and plugins allowed in a mode into one regex (and
        // again into the closer scans of formatting modes), and PCRE's limit on its compiled size is
        // the hard constraint here: exceeding it breaks every page with "A PCRE internal error
        // occured". So no sub-pattern may be repeated needlessly — e.g. the compound lists repeat
        // their item pattern only once (item(?:sep item)++). Subroutine calls to shared
        // sub-patterns are not an option: patterns can't contain capturing groups, and relative
        // references to the Lexer's per-pattern groups break in the closer scans, which join the
        // patterns without such groups.
        $add = function (string $pattern) use ($mode): void {
            $this->Lexer->addSpecialPattern($pattern, $mode, 'plugin_normi');
        };
        $artLaw = '(?:' . $artLawPattern . ')';

        // Compound: "Artikel 3, Artikel 4 Absatz 1, ..., die Artikel 16 bis 18 der Richtlinie 2008/115/EG"
        $singleItemPat = '(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?' . $subPartsInner . ')?';
        $compoundSep   = '(?:, (?:und (?:die |den )?)?| und (?:die |den )?)(?=(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels))';
        $add('!?' . $singleItemPat . '(?:' . $compoundSep . $singleItemPat . ')++ ' . $artPfx . $artLaw);

        $add('!?(?:des )?§(?:§)? [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subPartsList . ' ' . $artPfx . '(?:' . $nationalPattern . ')');

        // Compound §-list without repeated "§": "§§ 2 Abs. 3, 5 Abs. 1 Nr. 1 AufenthG", also plain
        // "§§ 25, 26 AufenthG" (no separate pattern for those, to stay within PCRE's size limit).
        // Items may also be reintroduced with "die"/"den" ("die §§ 13, 14 Absatz 2") and carry a
        // "bis" range ("§§ 85 bis 88"). Uses its own $buildSubParts call (not the shared
        // $subPartsInner) so the Nummer qualifier and the "Satz 6 und 7"/"Nummer 4 und 8" lists are
        // paid for only here, where they keep such a number attached to its own § instead of being
        // mistaken for the next item; parseMatch()'s tokenizer shrinks a greedy match if it
        // swallows the next item's number.
        $sectionItemSubParts = $buildSubParts('[0-9]+[a-z]?', '(?:und|oder|sowie)', true, true, true);
        $sectionItemPat = '(?:(?:die |den )?(?:des )?§(?:§)? )?[0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?(?:' . $sectionItemSubParts . ')?';
        $sectionSep     = '(?:,| und| oder| sowie) ';
        $this->lexerSectionItemPattern = $sectionItemPat;
        $this->lexerSectionSepPattern  = $sectionSep;
        // The item loop is possessive: otherwise a long list NOT followed by a known law ("§§ 86,
        // 86a, …, 306 bis 306c des Strafgesetzbuches") backtracks exponentially (roughly doubling
        // per item) and trips pcre.backtrack_limit. Backtracking still happens within one
        // separator+item, so a greedy Absatz list can still give back a number to the next item.
        // The separator lookahead keeps an item from swallowing an "und" that belongs to its own
        // "und Absatz …" tail, which the possessive loop could no longer give back.
        $sectionSepGuarded = $sectionSep . '(?=(?:die |den )?(?:des )?§|[0-9])';
        $add(
            // (?<![a-zA-ZäöüßÄÖÜ]) guards the optional "die|den" against matching as the tail of an
            // unrelated word (e.g. "fin-den § 3 ..." must not be read as "den § 3 ...").
            '!?(?<![a-zA-ZäöüßÄÖÜ])(?:die |den )?(?:des )?§(?:§)? ' . $sectionItemPat . '(?:' . $sectionSepGuarded . $sectionItemPat . ')++ ' . $artPfx . '(?:' . $nationalPattern . ')'
        );

        // Also handles "Artikeln 1 und 79 Absatz 3 der Verordnung (EU) 2024/1348": the trailing
        // $subParts (optional) lets the LAST item carry Absatz/Unterabsatz/… before the regulation.
        // The optional (?: bis N)? in each slot also covers "Artikeln 20 bis 26 und 28 bis 35 der …"
        // without a new Lexer alternation.
        $add('!?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?(?:(?:,| und| oder| sowie) [0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?)+' . $subParts . ' ' . $artPfx . $artLaw);

        $add('!?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?: f{1,2}\.?| bis [0-9]+[a-z]?)?' . $subPartsList . ' ' . $artPfx . $artLaw);

        // Bare "Artikel 25 bis 28 und 34", "Artikel 25 bis 28", "Artikel 3 und 4 sowie 6 bis 7" (no
        // explicit law — falls back to the current page's regulation): a range, or a list of at
        // least two items, any of which may be a range
        $artBisListInner = self::BARE_LIST;
        $add('!?(?:Art\.|Artikel|Artikeln|des Artikels) ' . $artBisListInner);

        // Possessive [0-9]++[a-z]?+ so the trailing lookaheads can't be satisfied by backtracking
        // into a shorter article number (e.g. matching just "2" out of "25 bis 28")
        $add('!?(?:Art\.|Artikel|des Artikels) [0-9]++[a-z]?+(?:' . $subPartsListInner . ')?+(?!, [0-9])(?! bis [0-9])');

        // Bare "§§ 16a bis 16c, 16e sowie 16f" (no explicit law — falls back to the current page's
        // regulation). Not if Absatz/Satz/Nummer follows: then the last item carries sub-parts and
        // the next pattern takes the whole list.
        $add('!?(?:des )?§(?:§)? (?>' . $artBisListInner . ')(?! (?:Abs|Satz|S\.|Nummer|Nr\.|Unterabs|UA))');

        // Bare "§ 5 Absatz 1", also after a list of bare numbers: "§§ 12, 13 Absatz 1 in Verbindung
        // mit Absatz 2 Nummer 1"
        $add('!?(?:des )?§(?:§)? (?:[0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?(?:,| und| oder| sowie) (?=[0-9]))*+[0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?(?!(?:,| und| oder| sowie) [0-9])' . $subPartsList);

        $add('!?(?:' . $synonymPattern . '|' . $euPattern . ')');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        // Leading "!" (mirroring DokuWiki's own "!CamelCase" escape convention) tells normi to
        // leave this reference as plain text, e.g. so another plugin (dejure.org-style law linkers)
        // can pick it up — useful for norms not mapped to a page in this wiki.
        $ignore = false;
        if (substr($match, 0, 1) === '!') {
            $ignore = true;
            $match = substr($match, 1);
        }
        $data = $this->parseMatch($match);
        $data['ignore'] = $ignore;
        if (!$ignore && $data['regulation'] !== null && $data['regulation'] !== '__current__') {
            self::inheritBackwards($handler, $data);
        }
        return $data;
    }

    /** Text allowed between two citations of one list: connectors, articles, prepositions. */
    public const INHERIT_GAP = '/^[,;]?(?: (?:und|oder|sowie|bzw\.|beziehungsweise|und\/oder|auch|ferner|die|der|das|den|dem|des|nach|gemäß|aus|in|im|von|zu|auf))* $/';
    /** …but the gap must contain an actual list connector. */
    public const INHERIT_CONNECTOR = '/^[,;]| (?:und|oder|sowie|bzw\.|beziehungsweise|und\/oder) /';

    /** Whether the text between two citations joins them into one list. */
    public static function joinsList(string $gap): bool
    {
        return preg_match(self::INHERIT_GAP, $gap) && preg_match(self::INHERIT_CONNECTOR, $gap);
    }

    /**
     * The plain text a call stands for, or null if it is markup. Container plugins (e.g. the
     * blockquote plugin's <blockquote>) emit the text inside them as their own plugin calls in
     * the DOKU_LEXER_UNMATCHED state instead of "cdata".
     */
    public static function callText(array $call): ?string
    {
        if ($call[0] === 'cdata') {
            return $call[1][0];
        }
        if ($call[0] === 'plugin' && ($call[1][2] ?? null) === DOKU_LEXER_UNMATCHED && is_string($call[1][3] ?? null)) {
            return $call[1][3];
        }
        return null;
    }

    /** "§" or "Art" for a linkable citation, null for an ignored one or a bare regulation name. */
    public static function citationRunKind(array $data): ?string
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

    /**
     * "nach § 12 Absatz 1 oder § 13 Absatz 1 sowie die nach § 26 Absatz 1 der
     * Deutschsprachförderverordnung": the law is named only once, after the last item, but
     * applies to the bare ("__current__") citations before it too. When the citation with the
     * law is handled, the preceding ones are already in the handler's call list; a run of them
     * joined only by connectors takes over its regulation. (action.php does the same on the
     * finished call list, which also covers tables; this here works even if no action hooks run.)
     */
    private static function inheritBackwards(Doku_Handler $handler, array $data): void
    {
        $kind = self::citationRunKind($data);
        // Only when calls go straight into the handler's list (not buffered by a table/list/footnote rewriter)
        if ($kind === null || get_class($handler->getCallWriter()) !== \dokuwiki\Parsing\Handler\CallWriter::class) {
            return;
        }
        $calls =& $handler->calls;
        $i = count($calls) - 1;
        while (true) {
            $gap = '';
            while ($i >= 0 && ($text = self::callText($calls[$i])) !== null) {
                $gap = $text . $gap;
                $i--;
            }
            if ($i < 0 || !self::joinsList($gap)
                || $calls[$i][0] !== 'plugin' || $calls[$i][1][0] !== 'normi'
                || $calls[$i][1][1]['regulation'] !== '__current__'
                || self::citationRunKind($calls[$i][1][1]) !== $kind) {
                return;
            }
            $calls[$i][1][1]['regulation'] = $data['regulation'];
            $i--;
        }
    }

    private function parseMatch(string $match): array
    {
        // Compound: "Artikel 3, Artikel 4 Absatz 1, ..., die Artikel 16 bis 18 der Richtlinie"
        // Also handles: "Artikeln 1 und 79 Absatz 3 der Verordnung (EU) 2024/1348"
        // (subsequent items are bare digits without a repeated "Artikel" prefix)
        if (!empty($this->lexerSynonymPattern)) {
            $regPfxPat = '(?:der |des |dem |die |den )?';
            if (preg_match('~ ' . $regPfxPat . '(' . $this->lexerSynonymPattern . '|' . $this->lexerEuPattern . '|' . $this->lexerExtArticlePattern . ')$~', $match, $rm)) {
                $regSlug = $this->resolveRegulation($rm[1], 'article');
                if ($regSlug !== null) {
                    $regSuffix = $rm[0];
                    $itemsText = substr($match, 0, -strlen($regSuffix));
                    // If itemsText is actually just ONE Artikel item with a full subParts tail
                    // (e.g. "Artikel 5 Absatz 1 und 3"), it's not a compound at all — the "und 3"
                    // belongs to the Absatz-list, not a second Artikel. Skip and let the single-item
                    // branch below handle it as one cohesive reference.
                    $singleItemFullPat = '/^(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?(?:(?: bis [0-9]+[a-z]?)?' . $this->lexerSubPartsPattern . ')?$/';
                    // §-references are left to the §-branch below; otherwise "§ 1 Abs. 1 Nr. 1, 3 und 4 AsylbLG"
                    // would be split here and lose its first item, which does not parse as an Artikel
                    $isArticleList = preg_match('/^(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) /', $itemsText);
                    if ($isArticleList && !preg_match($singleItemFullPat, $itemsText)) {
                        // Prefer splitting on "Artikel"-introducing separators (compound with repeated keyword);
                        // fall back to bare-digit lookahead for "Artikeln N und M Absatz K" constructs.
                        $sepPat = '/(?:, (?:und (?:die |den )?)?| und (?:die |den )?)(?=(?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels))/';
                        $items = preg_split($sepPat, $itemsText);
                        if (count($items) < 2) {
                            $sepPat = '/(?:,| und| oder| sowie) (?=[0-9])/';
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
                                } elseif (preg_match('/^([0-9]+[a-z]?) bis ([0-9]+[a-z]?)/', $itemText, $nm)) {
                                    // Bare-digit range (e.g. "28 bis 35" as a continuation of "Artikeln 20 bis 26 und …")
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($nm[1]), 'article_to' => strtolower($nm[2])];
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
                $regSlug = $this->resolveRegulation($rm[1], 'paragraph');
                if ($regSlug !== null) {
                    $regSuffix = $rm[0];
                    $itemsText = substr($match, 0, -strlen($regSuffix));
                    // If itemsText is actually just ONE § item with a full subParts tail
                    // (e.g. "§ 60 Absatz 5 und 7"), it's not a compound at all — the "und 7"
                    // belongs to the Absatz-list, not a second §. Skip and let the single-item
                    // branch below handle it as one cohesive reference.
                    $singleItemFullPat = '/^(?:(?:die |den )?(?:des )?§(?:§)? )?[0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?(?:' . $this->lexerSubPartsPattern . ')?$/';
                    if (!preg_match($singleItemFullPat, $itemsText)) {
                        // Tokenize with the SAME item/separator grammar the Lexer pattern above is
                        // built from, instead of a naive comma/digit heuristic — otherwise a
                        // multi-value Absatz/Nummer chain within one § (e.g. "Absatz 1 Satz 6 und 7",
                        // "Absatz 2 und 3 Nummer 2 erste Alternative") gets mistaken for the start
                        // of the next item.
                        $itemRe = '/^' . $this->lexerSectionItemPattern . '/u';
                        $sepRe  = '/^' . $this->lexerSectionSepPattern . '/u';
                        $items = [];
                        $connectors = [];
                        $pos = 0;
                        $len = strlen($itemsText);
                        while ($pos < $len) {
                            if (!preg_match($itemRe, substr($itemsText, $pos), $im) || $im[0] === '') {
                                break;
                            }
                            $itemMatch = $im[0];
                            // The item regex is greedy: its trailing Absatz value list (e.g.
                            // "Abs. 3, 5") may have swallowed a bare number that was actually meant
                            // to start the NEXT item (e.g. "§§ 2 Abs. 3, 5 Abs. 1 Nr. 1 AufenthG" is
                            // "§ 2 Abs. 3" + "§ 5 Abs. 1 Nr. 1", not "§ 2 Abs. 3, 5" + "Abs. 1 Nr. 1").
                            // If no separator follows the greedy match, shrink it one comma-value at
                            // a time and retry — this is only safe to do while the shrunk text is
                            // still itself a complete, valid item.
                            while (true) {
                                $afterLen = $pos + strlen($itemMatch);
                                $hasSep = $afterLen >= $len || preg_match($sepRe, substr($itemsText, $afterLen));
                                if ($hasSep) {
                                    break;
                                }
                                if (!preg_match('/^(.*),\s*[0-9]+[a-z]?$/', $itemMatch, $shrink)
                                    || !preg_match('/^' . $this->lexerSectionItemPattern . '$/u', $shrink[1])) {
                                    break;
                                }
                                $itemMatch = $shrink[1];
                            }
                            $items[] = $itemMatch;
                            $pos += strlen($itemMatch);
                            if ($pos >= $len) {
                                break;
                            }
                            if (!preg_match($sepRe, substr($itemsText, $pos), $sm)) {
                                break;
                            }
                            $connectors[] = $sm[0];
                            $pos += strlen($sm[0]);
                        }
                        if ($pos === $len && count($items) >= 2) {
                            $parsedItems = [];
                            foreach ($items as $idx => $itemText) {
                                $displayText = ($idx === count($items) - 1) ? $itemText . $regSuffix : $itemText;
                                if (preg_match('/^(?:(?:die |den )?(?:des )?§(?:§)? )?([0-9]+[a-z]?) bis ([0-9]+[a-z]?)/', $itemText, $nm)) {
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($nm[1]), 'article_to' => strtolower($nm[2])];
                                } elseif (preg_match('/^(?:(?:die |den )?(?:des )?§(?:§)? )?([0-9]+[a-z]?)/', $itemText, $nm)) {
                                    $parsedItems[] = ['text' => $displayText, 'article' => strtolower($nm[1]), 'article_to' => null];
                                }
                            }
                            if (count($parsedItems) >= 2) {
                                return ['match' => $match, 'compound' => $parsedItems, 'connectors' => $connectors, 'regulation' => $regSlug];
                            }
                        }
                        // One item after all, whose tail only this item grammar knows (e.g. the
                        // Satz list in "§ 29 Absatz 1 Satz 1 oder 3 des Verwaltungsverfahrensgesetzes"):
                        // keep its law instead of falling through to the bare-§ branches below.
                        if ($pos === $len && count($items) === 1
                            && preg_match('/^(?:(?:die |den )?(?:des )?§(?:§)? )?([0-9]+[a-z]?)/', $items[0], $nm)) {
                            return ['match' => $match, 'article' => strtolower($nm[1]), 'regulation' => $regSlug];
                        }
                    }
                }
            }
        }

        // Bare "Artikel 25 bis 28 und 34" / "§§ 16a bis 16c, 16e sowie 16f" (no explicit law) — resolves via the current page's regulation
        // "§§ 12, 13 Absatz 1 in Verbindung mit Absatz 2 Nummer 1", "§§ 4 bis 7 Nummer 1 bis 10" (no
        // explicit law): bare numbers or ranges whose last item carries sub-parts
        if (preg_match('/^((?:des )?§(?:§)? )((?:[0-9]+[a-z]?(?: bis [0-9]+[a-z]?)?(?:,| und| oder| sowie) )*)([0-9]+[a-z]?)(?: bis ([0-9]+[a-z]?))?(' . $this->lexerSubPartsPattern . ')$/', $match, $m)
            && $m[5] !== '' && ($m[2] !== '' || $m[4] !== '')) {
            preg_match_all('/([0-9]+[a-z]?)(?: bis ([0-9]+[a-z]?))?((?:,| und| oder| sowie) )/', $m[2], $parts, PREG_SET_ORDER);
            $items = [];
            $connectors = [];
            foreach ($parts as $i => $part) {
                $to = ($part[2] ?? '') !== '' ? strtolower($part[2]) : null;
                $items[] = [
                    'text'       => ($i === 0 ? $m[1] : '') . $part[1] . ($to !== null ? ' bis ' . $part[2] : ''),
                    'article'    => strtolower($part[1]),
                    'article_to' => $to,
                ];
                $connectors[] = $part[3];
            }
            $items[] = [
                'text'       => ($m[2] === '' ? $m[1] : '') . $m[3] . ($m[4] !== '' ? ' bis ' . $m[4] : '') . $m[5],
                'article'    => strtolower($m[3]),
                'article_to' => $m[4] !== '' ? strtolower($m[4]) : null,
            ];
            return ['match' => $match, 'compound' => $items, 'connectors' => $connectors, 'regulation' => '__current__'];
        }

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )(' . self::BARE_LIST . ')$/', $match, $m)) {
            preg_match_all('/(?:^|((?:,| und| oder| sowie) ))([0-9]+[a-z]?)(?: bis ([0-9]+[a-z]?))?/', $m[2], $am, PREG_SET_ORDER);
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

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?)((?:(?:,| und| oder| sowie) [0-9]+[a-z]?)+) (?!(?:und|oder|sowie) [0-9])(.+)$/', $match, $m)) {
            preg_match_all('/((?:,| und| oder| sowie) )([0-9]+[a-z]?)/', $m[3], $parts, PREG_SET_ORDER);
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
                'regulation' => $this->resolveRegulation(preg_replace('/^(?:der|des|dem|die|den) /', '', $m[4]), self::citationKind($match)),
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?) bis ([0-9]+[a-z]?) (.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'prefix'     => $m[1],
                'article'    => strtolower($m[2]),
                'article_to' => strtolower($m[3]),
                'reg_text'   => $m[4],
                'regulation' => $this->resolveRegulation(preg_replace('/^(?:der|des|dem|die|den) /', '', $m[4]), self::citationKind($match)),
            ];
        }

        if (preg_match('/^(?:Art\.|Artikel|des Artikels|(?:des )?§(?:§)?) ([0-9]+[a-z]?)(?: f{1,2}\.?| bis [0-9]+[a-z]?)?(?:' . $this->lexerSubPartsPattern . ')? (?:der |des |dem |die |den )?(?!(?:Absatz|Abs\.|Absätze|Unterabsatz|Unterabsätze|Satz|S\.|Nummer|Nr\.|Buchstabe|Buchstaben|lit\.|Alternative|Alt\.)\s|bis\s|und\s|oder\s|in Verbindung mit\s|i\. ?V\. ?m\.|[0-9])(.+)$/', $match, $m)) {
            return [
                'match'      => $match,
                'article'    => strtolower($m[1]),
                'regulation' => $this->resolveRegulation($m[2], self::citationKind($match)),
            ];
        }

        if (preg_match('/^((?:Art\.|Artikel|Artikeln|des Artikels|(?:des )?§(?:§)?) )([0-9]+[a-z]?)((?:(?:,| und| oder| sowie) [0-9]+[a-z]?)+)$/', $match, $m)) {
            preg_match_all('/((?:,| und| oder| sowie) )([0-9]+[a-z]?)/', $m[3], $parts, PREG_SET_ORDER);
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

    /** "article" for an Art./Artikel citation, "paragraph" for a § citation. */
    private static function citationKind(string $match): string
    {
        return preg_match('/^(?:die |den )?(?:des )?§/', $match) ? 'paragraph' : 'article';
    }

    /**
     * @param string|null $external "article"/"paragraph": also accept laws without wiki pages
     *     (returned as "ext:a:<abbr>" / "ext:p:<abbr>"); null: only the wiki's own regulations
     */
    private function resolveRegulation(string $term, ?string $external = null): ?string
    {
        $slug = $this->resolveWikiRegulation($term);
        if ($slug !== null || $external === null) {
            return $slug;
        }
        $abbr = ExternalLaws::resolve($term, $external === 'article');
        return $abbr === null ? null : 'ext:' . $external[0] . ':' . $abbr;
    }

    private function resolveWikiRegulation(string $term): ?string
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
        if ($slug !== null) {
            return $slug === '__current__' ? null : $slug;
        }
        // Header looks like an EU-law citation but isn't mapped to a wiki page.
        // Signal "suppress" so bare article references in this column are treated as plain text.
        if (preg_match(
            '/^(?:Verordnung|Richtlinie) \(E[UG]\) (?:Nr\. )?[0-9]'
            . '|^Richtlinie [0-9]{4}\/[0-9]+(?:\/(?:EU|EG))?$'
            . '|^\(EU\) (?:Nr\. )?[0-9]/',
            $headerText
        )) {
            return '__suppress__';
        }
        return null;
    }

    /**
     * Infers the regulation for a bare "__current__" reference from the immediately preceding
     * rendered context, enabling "AEUV, insbesondere auf Artikel 77 und Artikel 79" to link both
     * articles to AEUV even when the page itself is about a different regulation.
     *
     * Two cases are handled:
     *   1. The first article after the connector: doc ends with "[,;]? insbesondere/namentlich [auf] [den|die|das] "
     *   2. Subsequent articles in the same list: doc ends with "</a> [und|oder|sowie] " and the
     *      preceding article was rendered using the same regulation as $lastLinkedRegulation.
     */
    private function resolveFromPrecedingContext(Doku_Renderer $renderer): ?string
    {
        if ($this->lastLinkedRegulation === null) {
            return null;
        }
        $tail = substr($renderer->doc, -150);
        if (preg_match('/[,;]? (?:insbesondere|namentlich)(?: auf)? (?:(?:den|die|das) )?$/', $tail)) {
            return $this->lastLinkedRegulation;
        }
        if ($this->lastArticleRegulation === $this->lastLinkedRegulation
            && preg_match('/<\/a>[,;]? (?:und |oder |sowie )?$/', $tail)) {
            return $this->lastLinkedRegulation;
        }
        return null;
    }

    /** false = not yet resolved, null = not found, string = slug */
    private $resolvedCurrentRegulation = false;

    /** false = not yet resolved, null = not found, string = article number */
    private $resolvedCurrentArticle = false;

    /** Slug of the most recently rendered standalone regulation link. */
    private $lastLinkedRegulation = null;

    /** Slug used for the most recently rendered article link; used to extend the active regulation
     *  context to subsequent articles in the same "insbesondere/namentlich" list. */
    private $lastArticleRegulation = null;

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

        if (!empty($data['ignore'])) {
            $renderer->doc .= hsc($data['match']);
            return true;
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
            if ($tableRegulation === '__suppress__') {
                $renderer->doc .= hsc($data['match']);
                return true;
            }
            if ($tableRegulation !== null) {
                $regulation = $tableRegulation;
            } else {
                // "AEUV, insbesondere auf Artikel 77 und Artikel 79" — infer from preceding rendered text.
                $contextRegulation = $this->resolveFromPrecedingContext($renderer);
                if ($contextRegulation !== null) {
                    $regulation = $contextRegulation;
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
        }

        if (isset($data['compound'])) {
            $this->lastArticleRegulation = $regulation;
            foreach ($data['compound'] as $i => $item) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                if ($item['article_to'] !== null
                    && (preg_match('/^((?:die |den )?(?:Art\.|Artikel|Artikeln|des Artikels) [0-9]+[a-z]?) bis (.+)$/', $item['text'], $bm)
                        || preg_match('/^((?:die |den )?(?:des )?§(?:§)? [0-9]+[a-z]?) bis (.+)$/', $item['text'], $bm)
                        || preg_match('/^([0-9]+[a-z]?) bis (.+)$/', $item['text'], $bm))) {
                    $this->normLink($renderer, $regulation, $item['article'], $bm[1]);
                    $renderer->doc .= ' bis ';
                    $this->normLink($renderer, $regulation, $item['article_to'], $bm[2]);
                } else {
                    $this->normLink($renderer, $regulation, $item['article'], $item['text']);
                }
            }
            return true;
        }

        if (isset($data['rangelist'])) {
            $this->lastArticleRegulation = $regulation;
            foreach ($data['rangelist'] as $i => $item) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                $fromText = $i === 0 ? $data['prefix'] . $item['article'] : $item['article'];
                $this->normLink($renderer, $regulation, $item['article'], $fromText);
                if ($item['article_to'] !== null) {
                    $renderer->doc .= ' bis ';
                    if ((int) $item['article_to'] < (int) $item['article']) {
                        // Not a range but a date: "Artikeln 1, 2, … und 35 bis 21. Dezember 2013"
                        $renderer->doc .= hsc($item['article_to']);
                    } else {
                        $this->normLink($renderer, $regulation, $item['article_to'], $item['article_to']);
                    }
                }
            }
            return true;
        }

        if (isset($data['article_to'])) {
            $this->lastArticleRegulation = $regulation;
            $this->normLink($renderer, $regulation, $data['article'], $data['prefix'] . $data['article']);
            $renderer->doc .= ' bis ';
            $this->normLink($renderer, $regulation, $data['article_to'], $data['article_to'] . ' ' . $data['reg_text']);
            return true;
        }

        if (isset($data['articles'])) {
            $this->lastArticleRegulation = $regulation;
            $count = count($data['articles']);
            foreach ($data['articles'] as $i => $article) {
                if ($i > 0) {
                    $renderer->doc .= hsc($data['connectors'][$i - 1]);
                }
                if ($i === 0) {
                    $linkText = $data['prefix'] . $article;
                } elseif ($i === $count - 1) {
                    $linkText = $data['reg_text'] !== null ? $article . ' ' . $data['reg_text'] : $article;
                } else {
                    $linkText = $article;
                }
                $this->normLink($renderer, $regulation, strtolower($article), $linkText);
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
            $this->lastLinkedRegulation = $regulation;
        } else {
            $this->lastArticleRegulation = $regulation;
        }

        $this->normLink($renderer, $regulation, $article, $data['match']);

        return true;
    }

    /**
     * Link one article/§ (or, with $article null, a regulation's start page). Regulations
     * without wiki pages ("ext:…") are linked to dejure.org, buzer.de or gesetze-im-internet.de,
     * whichever carries the law first; if none does, the text is left unlinked.
     */
    private function normLink(Doku_Renderer $renderer, string $regulation, ?string $article, string $text): void
    {
        if (strncmp($regulation, 'ext:', 4) !== 0) {
            $pageId = $article === null ? self::START_PAGES[$regulation] : 'art._' . $article . '_' . $regulation;
            $this->previewLink($renderer, $pageId, $text);
            return;
        }
        $url = null;
        if ($article !== null) {
            $provisional = false;
            $url = ExternalLaws::url(substr($regulation, 6), $article, $regulation[4] === 'a', $provisional);
            if ($provisional) {
                // A site did not answer yet: don't keep this rendering in the page cache.
                $renderer->nocache();
            }
        }
        if ($url === null) {
            $renderer->doc .= hsc($text);
            return;
        }
        $renderer->externallink($url, $text);
    }

    /**
     * Emit an internal link marked with data-normi-preview, so script.js can show
     * a hover preview of the target page.
     */
    private function previewLink(Doku_Renderer $renderer, string $id, string $text): void
    {
        if (!($renderer instanceof Doku_Renderer_xhtml)) {
            $renderer->internallink($id, $text);
            return;
        }
        $html = $renderer->internallink($id, $text, null, true);
        $renderer->doc .= preg_replace('/<a /', '<a data-normi-preview="1" ', $html, 1);
    }
}
