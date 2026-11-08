<?php

namespace dokuwiki\plugin\normi;

use dokuwiki\HTTP\DokuHTTPClient;

/**
 * Laws that have no pages in this wiki: recognition (long forms, abbreviations) and
 * the external link fallback dejure.org → buzer.de → gesetze-im-internet.de.
 *
 * Whether a site carries a law is checked once per law over HTTP and remembered
 * in DokuWiki's cache directory, so rendering does not depend on live lookups and
 * dejure.org's rate limit (HTTP 429 after a handful of quick requests) is not hit.
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Marcel Keienborg <marcel@aufentha.lt>
 */
class ExternalLaws
{
    /**
     * Abbreviation => long forms (nominative, genitive, dative) as cited in running text.
     * The abbreviation itself is recognised generically (see genericAbbreviationPattern()),
     * so it only needs to be listed here if it has a long form. Laws cited with "§".
     */
    const PARAGRAPH_LAWS = [
        'BGB'          => ['Bürgerliches Gesetzbuch', 'Bürgerlichen Gesetzbuches', 'Bürgerlichen Gesetzbuchs', 'Bürgerlichen Gesetzbuch'],
        'StGB'         => ['Strafgesetzbuch', 'Strafgesetzbuches', 'Strafgesetzbuchs'],
        'StPO'         => ['Strafprozessordnung', 'Strafprozeßordnung'],
        'ZPO'          => ['Zivilprozessordnung', 'Zivilprozeßordnung'],
        'HGB'          => ['Handelsgesetzbuch', 'Handelsgesetzbuches', 'Handelsgesetzbuchs'],
        'AO'           => ['Abgabenordnung'],
        'VwVfG'        => ['Verwaltungsverfahrensgesetz', 'Verwaltungsverfahrensgesetzes'],
        'VwZG'         => ['Verwaltungszustellungsgesetz', 'Verwaltungszustellungsgesetzes'],
        'GVG'          => ['Gerichtsverfassungsgesetz', 'Gerichtsverfassungsgesetzes'],
        'SGG'          => ['Sozialgerichtsgesetz', 'Sozialgerichtsgesetzes'],
        'FGO'          => ['Finanzgerichtsordnung'],
        'ArbGG'        => ['Arbeitsgerichtsgesetz', 'Arbeitsgerichtsgesetzes'],
        'FamFG'        => ['Gesetz über das Verfahren in Familiensachen und in den Angelegenheiten der freiwilligen Gerichtsbarkeit', 'Gesetzes über das Verfahren in Familiensachen und in den Angelegenheiten der freiwilligen Gerichtsbarkeit'],
        'GKG'          => ['Gerichtskostengesetz', 'Gerichtskostengesetzes'],
        'RVG'          => ['Rechtsanwaltsvergütungsgesetz', 'Rechtsanwaltsvergütungsgesetzes'],
        'ZustAnpG'     => ['Zuständigkeitsanpassungsgesetz', 'Zuständigkeitsanpassungsgesetzes'],
        'BVerfGG'      => ['Bundesverfassungsgerichtsgesetz', 'Bundesverfassungsgerichtsgesetzes'],
        'IRG'          => ['Gesetz über die internationale Rechtshilfe in Strafsachen', 'Gesetzes über die internationale Rechtshilfe in Strafsachen'],
        'JGG'          => ['Jugendgerichtsgesetz', 'Jugendgerichtsgesetzes'],
        'StVollzG'     => ['Strafvollzugsgesetz', 'Strafvollzugsgesetzes'],
        'BZRG'         => ['Bundeszentralregistergesetz', 'Bundeszentralregistergesetzes'],
        'BtMG'         => ['Betäubungsmittelgesetz', 'Betäubungsmittelgesetzes'],
        'StVG'         => ['Straßenverkehrsgesetz', 'Straßenverkehrsgesetzes'],
        'IfSG'         => ['Infektionsschutzgesetz', 'Infektionsschutzgesetzes'],
        'GenDG'        => ['Gendiagnostikgesetz', 'Gendiagnostikgesetzes'],
        'SchKG'        => ['Schwangerschaftskonfliktgesetz', 'Schwangerschaftskonfliktgesetzes'],
        'BÄO'          => ['Bundesärzteordnung'],
        'PAuswG'       => ['Personalausweisgesetz', 'Personalausweisgesetzes'],
        'PaßG'         => ['Passgesetz', 'Passgesetzes', 'Paßgesetz', 'Paßgesetzes'],
        'PStG'         => ['Personenstandsgesetz', 'Personenstandsgesetzes'],
        'BMG'          => ['Bundesmeldegesetz', 'Bundesmeldegesetzes'],
        'AZRG'         => ['Ausländerzentralregistergesetz', 'Ausländerzentralregistergesetzes'],
        'ATDG'         => ['Antiterrordateigesetz', 'Antiterrordateigesetzes'],
        'BPolG'        => ['Bundespolizeigesetz', 'Bundespolizeigesetzes'],
        'LuftVG'       => ['Luftverkehrsgesetz', 'Luftverkehrsgesetzes'],
        'TKG'          => ['Telekommunikationsgesetz', 'Telekommunikationsgesetzes'],
        'BArchG'       => ['Bundesarchivgesetz', 'Bundesarchivgesetzes'],
        'BGebG'        => ['Bundesgebührengesetz', 'Bundesgebührengesetzes'],
        'VerpflG'      => ['Verpflichtungsgesetz', 'Verpflichtungsgesetzes'],
        'BVFG'         => ['Bundesvertriebenengesetz', 'Bundesvertriebenengesetzes'],
        'BDSG'         => ['Bundesdatenschutzgesetz', 'Bundesdatenschutzgesetzes'],
        'EStG'         => ['Einkommensteuergesetz', 'Einkommensteuergesetzes'],
        'BAföG'        => ['Bundesausbildungsförderungsgesetz', 'Bundesausbildungsförderungsgesetzes'],
        'RBEG'         => ['Regelbedarfs-Ermittlungsgesetz', 'Regelbedarfs-Ermittlungsgesetzes'],
        'BKGG'         => ['Bundeskindergeldgesetz', 'Bundeskindergeldgesetzes'],
        'BEEG'         => ['Bundeselterngeld- und Elternzeitgesetz', 'Bundeselterngeld- und Elternzeitgesetzes'],
        'UVG'          => ['Unterhaltsvorschussgesetz', 'Unterhaltsvorschussgesetzes'],
        'WoGG'         => ['Wohngeldgesetz', 'Wohngeldgesetzes'],
        'BSHG'         => ['Bundessozialhilfegesetz', 'Bundessozialhilfegesetzes'],
        'MiLoG'        => ['Mindestlohngesetz', 'Mindestlohngesetzes'],
        'AÜG'          => ['Arbeitnehmerüberlassungsgesetz', 'Arbeitnehmerüberlassungsgesetzes'],
        'AEntG'        => ['Arbeitnehmer-Entsendegesetz', 'Arbeitnehmer-Entsendegesetzes'],
        'SchwarzArbG'  => ['Schwarzarbeitsbekämpfungsgesetz', 'Schwarzarbeitsbekämpfungsgesetzes'],
        'PflVG'        => ['Pflichtversicherungsgesetz', 'Pflichtversicherungsgesetzes'],
        'AdWirkG'      => ['Adoptionswirkungsgesetz', 'Adoptionswirkungsgesetzes'],
        'IntV'         => ['Integrationskursverordnung'],
        'DeuFöV'       => ['Deutschsprachförderverordnung'],
        'AsylVfG'      => ['Asylverfahrensgesetz', 'Asylverfahrensgesetzes'],
        'AuslG'        => ['Ausländergesetz', 'Ausländergesetzes'],
    ];

    /** Laws cited with "Art."/"Artikel" (same structure as PARAGRAPH_LAWS). */
    const ARTICLE_LAWS = [
        'GG'   => ['Grundgesetz', 'Grundgesetzes', 'Grundgesetzes für die Bundesrepublik Deutschland', 'Grundgesetz für die Bundesrepublik Deutschland'],
        'EMRK' => ['Europäische Menschenrechtskonvention', 'Europäischen Menschenrechtskonvention',
                   'Konvention zum Schutze der Menschenrechte und Grundfreiheiten', 'Konvention zum Schutz der Menschenrechte und Grundfreiheiten'],
        'GRCh' => ['Charta der Grundrechte der Europäischen Union', 'Grundrechtecharta', 'GRC', 'EUGrCh', 'GrCh', 'GrCH'],
        'EUV'  => ['Vertrag über die Europäische Union', 'Vertrags über die Europäische Union'],
    ];

    /** Books of the Sozialgesetzbuch: roman numeral => [number, ordinal stem ("Zwölft" → "Zwölften Buches")] */
    const SGB_BOOKS = [
        'I' => [1, 'Erst'], 'II' => [2, 'Zweit'], 'III' => [3, 'Dritt'], 'IV' => [4, 'Viert'],
        'V' => [5, 'Fünft'], 'VI' => [6, 'Sechst'], 'VII' => [7, 'Siebt'], 'VIII' => [8, 'Acht'],
        'IX' => [9, 'Neunt'], 'X' => [10, 'Zehnt'], 'XI' => [11, 'Elft'], 'XII' => [12, 'Zwölft'],
        'XIV' => [14, 'Vierzehnt'],
    ];

    /**
     * Where a site's slug for a law differs from the transliterated abbreviation. dejure.org
     * needs few: it redirects variants itself (AUeG → AUEG, VwVfG → BVwVfG), and the slug it
     * ends up at is remembered.
     */
    const SLUG_OVERRIDES = [
        'dejure' => ['EMRK' => 'MRK'],
        'buzer'  => [],
        'gii'    => ['BÄO' => 'b_o', 'AÜG' => 'a_g', 'BAföG' => 'baf_g', 'DeuFöV' => 'deuf_v', 'BVFG' => 'bvfg'],
    ];

    /**
     * Two-or-more-capital tokens that look like a law abbreviation but are not one
     * (courts, journals, units of a norm, EU shorthands) — never resolved externally.
     */
    const NOT_LAWS = [
        'UA', 'UAbs', 'HS', 'Hs', 'EU', 'EG', 'EWG', 'EGV', 'VO', 'RL', 'ABl', 'BAMF', 'BMI', 'ABH',
        'BVerwG', 'BVerfG', 'BGH', 'BSG', 'BAG', 'BFH', 'EuGH', 'EGMR', 'OVG', 'VGH', 'VG', 'LSG', 'SG',
        'OLG', 'LG', 'AG', 'KG', 'NJW', 'NVwZ', 'InfAuslR', 'ZAR', 'JZ', 'DVBl', 'BT', 'BR', 'BGBl',
    ];

    const CACHE_TTL         = 30 * 86400;  // definitive verdicts
    const CACHE_TTL_RETRY   = 3600;        // when a site did not answer (429, timeout, …)
    const LOOKUPS_PER_PAGE  = 6;           // uncached law lookups per page render …
    const LOOKUP_BUDGET     = 8.0;         // … and seconds they may take together

    /** @var array abbreviation => verdict, per request */
    private static $memo = [];
    private static $lookupsDone = 0;
    private static $lookupSeconds = 0.0;

    /** All long forms and the SGB abbreviations for "§" citations, mapped to the abbreviation. */
    public static function paragraphForms(): array
    {
        $forms = [];
        foreach (self::PARAGRAPH_LAWS as $abbr => $longForms) {
            foreach ($longForms as $form) {
                $forms[$form] = $abbr;
            }
        }
        foreach (self::SGB_BOOKS as $roman => [, $stem]) {
            $abbr = 'SGB ' . $roman;
            $forms[$abbr] = $abbr;
            $forms[$stem . 'en Buches Sozialgesetzbuch'] = $abbr;
            $forms[$stem . 'en Buches des Sozialgesetzbuches'] = $abbr;
            $forms[$stem . 'en Buch Sozialgesetzbuch'] = $abbr;
            $forms[$stem . 'es Buch Sozialgesetzbuch'] = $abbr;
            $forms['Sozialgesetzbuch ' . $stem . 'es Buch'] = $abbr;
            $forms['Sozialgesetzbuches ' . $stem . 'es Buch'] = $abbr;
        }
        return $forms;
    }

    /** All long forms for "Art." citations, mapped to the abbreviation. */
    public static function articleForms(): array
    {
        $forms = [];
        foreach (self::ARTICLE_LAWS as $abbr => $longForms) {
            $forms[$abbr] = $abbr;
            foreach ($longForms as $form) {
                $forms[$form] = $abbr;
            }
        }
        return $forms;
    }

    // Regex building blocks. Byte mode: DokuWiki's Lexer compiles without /u, so umlauts are
    // spelled out as alternatives and the end guard rejects any UTF-8 lead byte (\xC3).
    // A "/" inside a character class must stay unescaped (the Lexer escapes it itself).
    const RE_UPPER = '(?:[A-Z]|\xC3[\x84\x96\x9C])';        // A-Z Ä Ö Ü
    const RE_LOWER = '(?:[a-z]|\xC3[\xA4\xB6\xBC\x9F])';    // a-z ä ö ü ß
    const RE_END   = '(?![A-Za-z0-9/-]|\xC3)';

    /**
     * Abbreviation shape: at least two capitals ("VwVfG", "BtMG", "UkraineAufenthÜV",
     * "FreizügG/EU"). No hyphens, so compounds such as "Asyl-Unterstützungsteams" or
     * "BMVI-Verordnung" fail the end guard (kept simple: PCRE's size limit on the Lexer).
     */
    private static function abbreviationShape(): string
    {
        $u = self::RE_UPPER;
        $l = self::RE_LOWER . '*';
        return $u . $l . '(?:' . $u . $l . ')+(?:\/EU)?';
    }

    /**
     * Lexer pattern for the law slot of a citation without a wiki page. It only checks the
     * *shape* of the law name (the table of long forms is far too large for PCRE's limit on
     * the Lexer's combined regex); resolve() then decides whether it really is a known law.
     * Tokens excluded here fall back to the plugin's previous behaviour (e.g. a bare
     * "Artikel 5" before "UA 2" still links to the current regulation).
     */
    public static function lexerPattern(bool $article): string
    {
        $end = self::RE_END;
        $abbr = '(?!(?:UA|UAbs|HS|Hs|EU|EG|VO|RL|ARB)' . $end . ')' . self::abbreviationShape() . $end;
        if ($article) {
            return '(?:Vertrags? über die Europäische Union|Charta der Grundrechte der Europäischen Union|Grundgesetz(?:es)?)'
                . $end . '|' . $abbr;
        }
        $u = self::RE_UPPER;
        $l = self::RE_LOWER;
        return
            // "Strafgesetzbuches", "Regelbedarfs-Ermittlungsgesetzes", "Zivilprozeßordnung" (not a bare "Verordnung")
            '(?!Verordnung)' . $u . '(?:' . $l . '|-' . $u . ')*(?:gesetz|ordnung)(?:buch(?:e?s)?|es)?' . $end
            // "Bürgerlichen Gesetzbuchs", "Zwölften Buches Sozialgesetzbuch"
            . '|' . $u . $l . '*(?:en|es) (?:Gesetzbuch(?:e?s)?|Buch(?:es)? (?:des )?Sozialgesetzbuch(?:es)?)' . $end
            . '|SGB [IVX]+' . $end
            // "AZR-Gesetzes", "BKA-Gesetz"
            . '|[A-Z]{2,}-Gesetz(?:es)?' . $end
            . '|' . $abbr;
    }

    /** Resolves a cited law term to an abbreviation, or null if it is not an external law. */
    public static function resolve(string $term, bool $article): ?string
    {
        $forms = $article ? self::articleForms() : self::paragraphForms();
        if (isset($forms[$term])) {
            return $forms[$term];
        }
        if (in_array($term, self::NOT_LAWS, true)) {
            return null;
        }
        // "AZR-Gesetz(es)" → AZRG, "BKA-Gesetz" → BKAG
        if (!$article && preg_match('~^([A-Z]{2,})-Gesetz(?:es)?$~', $term, $m)) {
            return $m[1] . 'G';
        }
        // Any other abbreviation-shaped token; long forms must be in the table.
        if (preg_match('~^' . self::abbreviationShape() . '$~', $term)) {
            return $term;
        }
        return null;
    }

    /**
     * URL of the given § / article of an external law, or null if no site carries the law
     * (or it could not be checked yet; then $provisional is set and the page must not be cached).
     */
    public static function url(string $abbr, string $number, bool $article, bool &$provisional): ?string
    {
        $verdict = self::verdict($abbr);
        if ($verdict['provisional']) {
            $provisional = true;
        }
        switch ($verdict['site']) {
            case 'dejure':
                $slug = $verdict['slug'] ?? self::slug('dejure', $abbr);
                return 'https://dejure.org/gesetze/' . rawurlencode($slug) . '/' . rawurlencode($number) . '.html';
            case 'buzer':
                return 'https://www.buzer.de/s1.htm?a=' . rawurlencode($number) . '&g=' . rawurlencode($abbr);
            case 'gii':
                return 'https://www.gesetze-im-internet.de/' . self::slug('gii', $abbr) . '/'
                    . ($article ? 'art_' : '__') . rawurlencode($number) . '.html';
        }
        return null;
    }

    private static function slug(string $site, string $abbr): string
    {
        if (isset(self::SLUG_OVERRIDES[$site][$abbr])) {
            return self::SLUG_OVERRIDES[$site][$abbr];
        }
        if ($site === 'gii') {
            if (preg_match('/^SGB ([IVX]+)$/', $abbr, $m)) {
                return 'sgb_' . self::SGB_BOOKS[$m[1]][0];
            }
            // gesetze-im-internet.de replaces every non-ASCII letter with "_"
            return strtolower(preg_replace('/[^A-Za-z0-9-]/u', '_', $abbr));
        }
        // dejure.org and buzer.de transliterate umlauts and use "_" for spaces and slashes
        $slug = strtr($abbr, ['Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);
        return strtr($slug, [' ' => '_', '/' => '_']);
    }

    /** @return array{site: ?string, provisional: bool, slug?: string} */
    private static function verdict(string $abbr): array
    {
        if (isset(self::$memo[$abbr])) {
            return self::$memo[$abbr];
        }
        $file = getCacheName('normi_law_' . $abbr, '.normi');
        $cached = @json_decode((string) @file_get_contents($file), true);
        if (is_array($cached) && ($cached['expires'] ?? 0) > time()) {
            unset($cached['expires'], $cached['law']);
            return self::$memo[$abbr] = $cached;
        }
        if (self::$lookupsDone >= self::LOOKUPS_PER_PAGE || self::$lookupSeconds >= self::LOOKUP_BUDGET) {
            // Too many new laws on one page (or slow sites): resolve the rest on a later view.
            return self::$memo[$abbr] = ['site' => null, 'provisional' => true];
        }
        self::$lookupsDone++;

        $start = microtime(true);
        $verdict = self::lookup($abbr);
        self::$lookupSeconds += microtime(true) - $start;
        io_saveFile($file, json_encode($verdict + [
            'law'     => $abbr,
            'expires' => time() + ($verdict['provisional'] ? self::CACHE_TTL_RETRY : self::CACHE_TTL),
        ]));
        return self::$memo[$abbr] = $verdict;
    }

    /** Asks dejure.org, buzer.de and gesetze-im-internet.de (in this order) whether they carry the law. */
    private static function lookup(string $abbr): array
    {
        $provisional = false;   // set when a site did not answer: a later site may not be the right one

        // dejure.org: 200 = carried (possibly after an internal rename, e.g. VwVfG → BVwVfG);
        // a redirect to buzer.de means dejure knows the law but leaves it to buzer.de.
        $url = 'https://dejure.org/gesetze/' . rawurlencode(self::slug('dejure', $abbr));
        for ($hop = 0; $hop < 3; $hop++) {
            [$status, $location] = self::probe($url);
            if ($status === 200) {
                return ['site' => 'dejure', 'provisional' => false, 'slug' => rawurldecode(basename(parse_url($url, PHP_URL_PATH)))];
            }
            if ($location !== '' && parse_url($location, PHP_URL_HOST) === 'dejure.org') {
                $url = $location;
                continue;
            }
            if ($location !== '' && str_ends_with((string) parse_url($location, PHP_URL_HOST), 'buzer.de')) {
                return ['site' => 'buzer', 'provisional' => false];
            }
            if ($status !== 404 && $status !== 410) {
                $provisional = true;
            }
            break;
        }

        // buzer.de: its norm lookup answers with the norm itself, or with a search page
        [$status, , $body] = self::probe('https://www.buzer.de/s1.htm?a=1&g=' . rawurlencode($abbr), 4096);
        if ($status === 200 && preg_match('~<title>\s*+(?!Suche)~i', $body)) {
            return ['site' => 'buzer', 'provisional' => $provisional];
        }
        if ($status !== 200 && $status !== 404) {
            $provisional = true;
        }

        [$status] = self::probe('https://www.gesetze-im-internet.de/' . rawurlencode(self::slug('gii', $abbr)) . '/');
        if ($status === 200) {
            return ['site' => 'gii', 'provisional' => $provisional];
        }
        if ($status !== 404) {
            $provisional = true;
        }
        return ['site' => null, 'provisional' => $provisional];
    }

    /**
     * One GET without following redirects. Only the start of the body is read: dejure.org
     * sends no Content-Length, so waiting for a larger limit would run into the timeout.
     *
     * @return array{0: int, 1: string, 2: string} status (<= 0 on network errors), redirect target, body start
     */
    private static function probe(string $url, int $maxBody = 1024): array
    {
        $http = new DokuHTTPClient();
        $http->timeout = 4;
        $http->max_redirect = 0;
        $http->max_bodysize = $maxBody;
        $http->max_bodysize_abort = false;
        $http->keep_alive = false;
        $body = $http->get($url);
        $status = (int) $http->status;
        $location = in_array($status, [301, 302, 303, 307, 308], true) ? (string) ($http->resp_headers['location'] ?? '') : '';
        return [$status, $location, (string) $body];
    }
}
