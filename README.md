# normi Plugin für DokuWiki

Erzeugt automatisch interne Links auf Wiki-Seiten für Artikel von EU-Rechtsakten des Gemeinsamen Europäischen Asylsystems (GEAS), EU-Primärrecht (AEUV) sowie für Paragraphen nationaler deutscher Gesetze. Normen, die das Wiki nicht führt, werden zu dejure.org, buzer.de oder gesetze-im-internet.de verlinkt.

## Installation

Das Plugin muss unter `lib/plugins/normi/` in der DokuWiki-Installation abgelegt werden — der Ordnername muss exakt `normi` lauten.

## Verwendung

Sobald das Plugin aktiv ist, werden Verweise auf Artikel von bekannten Rechtsakten automatisch verlinkt. Der Link zeigt stets auf die Seite des genannten Artikels.

### Präfixe

Artikel können mit folgenden Präfixen geschrieben werden: `Art.`, `Artikel`, `des Artikels` (Genitiv Singular), `Artikeln` (Dativ Plural bei Aufzählungen). Paragraphen nationaler Gesetze verwenden `§` oder `§§`; auch die Genitivform `des §` wird erkannt.

### Untereinheiten

Nach der Artikelnummer können optional Untereinheiten in folgender Reihenfolge angegeben werden:

| Einheit | Schreibweisen |
|---|---|
| Absatz | `Absatz`, `Abs.`, `Absätze` (Plural); Nummern durch `,`, `und`, `oder` getrennt oder als Bereich mit `bis` (z. B. `Absatz 1 bis 3, 5 und 7 bis 9` oder `Absatz 2 oder 3`); kann direkt mit `, Unterabsatz …` bzw. `, Unterabsätze …` fortgesetzt werden (z. B. `Absatz 1, Unterabsätze 2 bis 8`) |
| Unterabsatz | `Unterabsatz`, `Unterabsätze` (Plural), `UA`; Nummern/Bereiche wie bei Absatz |
| Satz | `Satz`, `S.` |
| Alternative | `Alternative`, `Alt.`; numerisch (z. B. `Satz 2 Alt. 2`) |
| Nummer | `Nummer`, `Nr.`; Nummern/Bereiche wie bei Absatz (z. B. `Nummer 1, 3 und 4` oder `Nr. 1 bis 3`) |
| Buchstabe | `Buchstabe`, `Buchstaben` (Plural), `Buchst.`, `lit. a)`, oder Kurzform `c)` (einzelner Kleinbuchstabe mit schließender Klammer, z. B. nach `Nr.`); mehrere Buchstaben als Liste (`Buchstabe a, b oder c`, `Buchstaben a b und c`) oder mit wiederholtem `Buchstabe`/`Buchstaben` (`Buchstabe b und Buchstabe d`); optional mit Zusatz `erste`/`zweite Alternative` oder `Variante` (z. B. `Buchstabe b erste Alternative`) |

Alle Untereinheiten sind optional und beeinflussen nur den Linktext, nicht das Linkziel.

### Aufzählungen und Bereiche

**Aufzählungen** mit Komma, „und", „oder" oder „sowie" werden erkannt (z. B. `Artikel 5 oder 7`, `§ 25, 26 und 27`): Jeder genannte Artikel erhält einen eigenen Link.

**Bereichsangaben** mit „bis" werden ebenfalls erkannt: Der erste und der letzte Artikel werden je einzeln verlinkt, „bis" bleibt als Klartext erhalten. Auch Kombinationen aus Bereichen und Aufzählungen ohne Rechtsaktangabe werden erkannt (z. B. `Artikel 25 bis 28 und 34`).

### Kontextbasierte Verlinkung

Wird kein Rechtsaktname genannt, versucht das Plugin, die gemeinte Norm aus dem Kontext der aktuellen Wiki-Seite zu ermitteln — zuerst aus der **Seitenüberschrift**, dann als Fallback aus der **Page-ID** (z. B. enthält `art._60a_aufenthaltsgesetz` den Slug `aufenthaltsgesetz`). Das gilt für:

- **Selbstverweise** wie `der vorliegenden Verordnung`, `der vorliegenden Richtlinie`, `des vorliegenden Gesetzes`
- **Bare Artikelverweise** ohne Rechtsaktangabe, z. B. `Artikel 42 Absätze 1 und 3`, `Artikeln 14 und 15` oder `Artikel 25 bis 28 und 34`
- **Bare Paragraphenverweise** ohne Gesetzesangabe, z. B. `§ 25 Abs. 3` oder `§§ 16b, 16c und 17`

Lässt sich weder aus der Überschrift noch aus der Page-ID eine bekannte Norm ableiten, bleibt der Ausdruck unverlinkt.

Vorrang vor dem Seitenkontext hat ein **nachfolgend genanntes Gesetz derselben Aufzählung**: In `nach § 12 Absatz 1 oder § 13 Absatz 1 sowie die nach § 26 Absatz 1 der Deutschsprachförderverordnung` gehören alle drei Paragraphen zur DeuFöV, ebenso in `Artikel 42 Absatz 1 oder Artikel 67 Absatz 10 der Verordnung (EU) 2024/1351` beide Artikel zur AMM-Verordnung. Voraussetzung ist, dass zwischen den Verweisen nur Bindewörter stehen (Komma, `und`, `oder`, `sowie`, `bzw.`, ggf. mit Artikel oder Präposition wie `sowie die nach`) und alle Verweise gleichartig sind (nur `§` oder nur `Artikel`). Ist die Zuordnung im Einzelfall falsch, lässt sich der Verweis mit `!` unverlinkt lassen.

### Verlinkung unterdrücken (`!`)

Ein vorangestelltes `!` (analog zu DokuWikis eigener `!CamelCase`-Schreibweise) weist normi an, die unmittelbar folgende Norm **überhaupt nicht** zu verlinken — weder ins Wiki noch extern (siehe [Normen ohne Wiki-Seite](#normen-ohne-wiki-seite)) — und stattdessen als reinen Text auszugeben, z. B. `!§ 14 BGB` oder `!Art. 5 Anerkennungsverordnung`. So bleibt der Text für andere Mechanismen (z. B. ein dejure.org-Verlinkungs-Plugin, das auf den gerenderten Text zugreift) zugänglich.

Seit normi Normen ohne Wiki-Seite selbst zu dejure.org, buzer.de oder gesetze-im-internet.de verlinkt, ist das `!` dafür meist nicht mehr nötig und kann nach und nach entfernt werden.

In **Tabellen** wird zusätzlich die Kopfzeile (erste Zeile) der jeweiligen Spalte herangezogen: Nennt die Kopfzelle einer Spalte einen Rechtsakt, werden bare Artikelverweise in dieser Spalte (ab der zweiten Zeile) auf diesen Rechtsakt statt auf die aktuelle Seite bezogen.

**Einschränkungen:**

Auf Seiten zu nationalen Gesetzen (AufenthG, AsylG) werden bare `Artikel`/`Art.`-Verweise **nicht** automatisch verlinkt, weil nationale Gesetze `§` verwenden und ein `Artikel`-Verweis dort regelmäßig eine andere (nicht im Wiki geführte) Rechtsquelle meint. Bare `§`-Verweise auf denselben Seiten werden dagegen weiterhin korrekt verlinkt.

EU-Richtlinien im alten Format `Richtlinie YYYY/NN/EG` (z. B. `Artikel 13 der Richtlinie 2009/52/EG`) werden als solche erkannt und — sofern nicht im Wiki geführt — unverlinkt ausgegeben.

### Beispiele

| Wikitext | Ergebnis |
|---|---|
| `Art. 5 Anerkennungsverordnung` | Ein Link auf Art. 5 |
| `Artikel 12a AsylverfahrensVO` | Ein Link auf Art. 12a |
| `Art. 1 f. AufnahmeRL` | Ein Link (f. beeinflusst nur den Linktext) |
| `Art. 7 Abs. 1 AMMVO` | Ein Link auf Art. 7 |
| `Art. 7 Abs. 2 Nr. 3 lit. b) AMMVO` | Ein Link auf Art. 7 |
| `Art. 46 Abs. 2 Satz 2 Alt. 2 AMMVO` | Ein Link auf Art. 46 |
| `Art. 2 Nr. 17 c) AMMVO` | Ein Link auf Art. 2 |
| `Artikel 42 Absätze 1 und 3 AVVO` | Ein Link auf Art. 42 |
| `Art. 42 Absatz 3 Buchstabe a, b oder c AVVO` | Ein Link auf Art. 42 |
| `Artikel 39 Absatz 3 der Verordnung (EU) 2024/1348` | Ein Link auf Art. 39 |
| `des Artikels 8 Absatz 7 der vorliegenden Richtlinie` | Ein Link (Norm aus Seitentitel) |
| `Art. 23, 24 AVVO` | Zwei Links (Art. 23 und 24) |
| `Art. 23, 24 und 25 AVVO` | Drei Links |
| `Art. 7 bis 9 Krisenverordnung` | Zwei Links (Art. 7 und Art. 9), „bis" als Klartext |
| `Artikel 42 Absätze 1 und 3` | Ein Link (Norm aus Seitentitel) |
| `Artikeln 14 und 15` | Zwei Links (Norm aus Seitentitel) |
| `Artikel 5 oder 7 der Verordnung (EU) 2024/1356` | Zwei Links (Art. 5 und Art. 7) |
| `Artikeln 1 und 79 Absatz 3 der Verordnung (EU) 2024/1348` | Zwei Links (Art. 1 und Art. 79) |
| `Artikel 25 bis 28 und 34` | Drei Links (Art. 25, 28 und 34; Norm aus Seitentitel) |
| `Artikel 23 Absatz 2 Unterabsatz 1 Buchstaben a b und c` | Ein Link auf Art. 23 |
| `Artikel 78 AEUV` | Ein Link auf Art. 78 |
| `!Art. 5 Anerkennungsverordnung` | Kein Link, reiner Text (Verlinkung unterdrückt) |

## Unterstützte Rechtsakte

### GEAS-Reform 2024

| Rechtsakt | Synonyme |
|---|---|
| Anerkennungsverordnung (EU) 2024/1347 | Qualifikationsverordnung, AnerkennungsVO, QualifikationsVO, QVO, Statusverordnung, StatusVO |
| Asylverfahrensverordnung (EU) 2024/1348 | AsylverfahrensVO, AVVO, AsylVfVO |
| Aufnahmerichtlinie (EU) 2024/1346 | AufnahmeRL, Aufnahmerichtlinie 2024, AufnahmeRL 2024 |
| Grenzrückführungsverordnung (EU) 2024/1349 | GrenzrückführungsVO, GrenzRüFüVO, GrenzRFVO |
| Resettlementverordnung (EU) 2024/1350 | ResettlementVO |
| Asyl- und Migrationsmanagement-Verordnung (EU) 2024/1351 | Asyl- und MigrationsmanagementVO, Asyl- und Migrationsmanagement-VO, AMM-VO, AMMVO |
| Screening-Konsistenz-Verordnung (EU) 2024/1352 | Screening-Konsistenz-VO |
| Screening-Verordnung (EU) 2024/1356 | Screening-VO |
| Eurodac-Verordnung (EU) 2024/1358 | Eurodac-VO, Eurodac-Verordnung 2024, Eurodac-VO 2024 |
| Krisenverordnung (EU) 2024/1359 | KrisenVO |
| EUAA-Verordnung (EU) 2021/2303 | EUAA-VO |

Auch ein nacktes `(EU) 2024/1347` ohne vorangestelltes „Verordnung"/„Richtlinie" wird erkannt, z. B. in nummerierten Aufzählungen wie:

```
1. (EU) 2024/1347,
2. (EU) 2024/1348, …
```

### Weitere Rechtsakte

| Rechtsakt | Synonyme |
|---|---|
| Rückführungsverordnung (EU) 2026/2280 | RückführungsVO |
| Assoziationsratsbeschluss EWG – Türkei Nr. 1/80 | ARB 1/80, Assoziationsratsbeschluss 1/80 (die Langformen auch im Genitiv „Assoziationsratsbeschlusses …“) |

### Ältere Rechtsakte (GEAS-System bis 2024)

| Rechtsakt | Synonyme |
|---|---|
| Aufnahmerichtlinie 2013/33/EU | Aufnahmerichtlinie 2013, AufnahmeRL 2013 |
| Qualifikationsrichtlinie 2011/95/EU | QualifikationsRL, Anerkennungsrichtlinie, AnerkennungsRL, QRL |
| Asylverfahrensrichtlinie 2013/32/EU | AsylverfahrensRL |
| Dublin-III-Verordnung (EU) Nr. 604/2013 | Dublin-III-VO, DublinVO, Dublin III, Dublin-III, Dublin III-Verordnung, Dublin III-VO |
| Eurodac-Verordnung (EU) Nr. 603/2013 | Eurodac-Verordnung 2013, Eurodac-VO 2013 |
| EASO-Verordnung (EU) Nr. 439/2010 | EASO-VO |
| Rückführungsrichtlinie 2008/115/EG | RückführungsRL |

Alle Synonyme und EU-Nummern eines Rechtsakts verlinken auf dieselbe Zielseite.

### EU-Primärrecht

| Rechtsakt | Synonyme |
|---|---|
| Vertrag über die Arbeitsweise der Europäischen Union | AEUV |

Der AEUV wird wie die übrigen EU-Rechtsakte mit `Artikel`/`Art.` zitiert (nicht mit `§`), hat aber keine EU-Nummer und keine Versionsdifferenzierung.

### Nationale Gesetze (Deutschland)

Bei nationalen Gesetzen werden Paragraphen mit `§` (Singular) oder `§§` (Plural) zitiert.

| Gesetz | Abkürzung |
|---|---|
| Aufenthaltsgesetz | AufenthG |
| Asylgesetz | AsylG |
| Asylbewerberleistungsgesetz | AsylbLG |
| Beschäftigungsverordnung | BeschV |
| Aufenthaltsverordnung | AufenthV |
| Staatsangehörigkeitsgesetz | StAG |
| Verwaltungsgerichtsordnung | VwGO |
| Freizügigkeitsgesetz/EU | FreizügG/EU |
| Ukraine-Aufenthalts-Übergangsverordnung | UkraineAufenthÜV |

**Beispiele:**

| Wikitext | Ergebnis |
|---|---|
| `§ 25 AufenthG` | Ein Link auf § 25 |
| `§ 25 Abs. 1 AufenthG` | Ein Link auf § 25 |
| `§ 3 AsylbLG` | Ein Link auf § 3 |
| `§§ 25, 26 AufenthG` | Zwei Links |
| `§§ 25 bis 27 AufenthG` | Zwei Links (§ 25 und § 27), „bis" als Klartext |
| `des § 3 Abs. 1 AufenthG` | Ein Link auf § 3 (Genitiv-Präfix) |
| `§ 26 Absatz 2 oder 3 des Asylgesetzes` | Ein Link auf § 26 |
| `§ 60 Abs. 1 bis 3, 5 und 7 bis 9 AufenthG` | Ein Link auf § 60 |
| `§ 60 Absatz 5 und 7 des Aufenthaltsgesetzes` | Ein Link auf § 60 (Absatz-Liste, kein zweiter Paragraph) |
| `§ 1 Absatz 1 Nummer 1, 3 und 4 AsylbLG` | Ein Link auf § 1 (Nummern-Liste, keine weiteren Paragraphen) |
| `§ 1 Absatz 1 Nummer 1, 3 und 4` | Ein Link auf § 1 (Norm aus Seitentitel) |
| `§ 68 Absatz 1 Satz 1 oder § 68a Absatz 1 Satz 1 des Asylgesetzes` | Zwei Links (§ 68 und § 68a AsylG) |
| `§ 81 Absatz 5 in Verbindung mit Absatz 3 oder Absatz 4 des Aufenthaltsgesetzes` | Ein Link auf § 81 AufenthG (auch `i. V. m.`/`i.V.m.`) |
| `§§ 16a bis 16c, 16e sowie 16f` | Vier Links (§ 16a, 16c, 16e und 16f; Norm aus Seitentitel) |
| `§ 25 Abs. 3` | Ein Link (Norm aus Seitentitel) |
| `§§ 16b, 16c, 16e und 17` | Vier Links (Norm aus Seitentitel) |
| `des § 3 Abs. 1` | Ein Link (Norm aus Seitentitel) |

### Versionsdifferenzierung bei Eurodac und Aufnahmerichtlinie

Da jeweils zwei Fassungen existieren (2013 und 2024), wird die Version über die Jahreszahl im Synonym oder über die vollständige EU-Nummer disambiguiert:

| Zitat | Zielseite |
|---|---|
| `Aufnahmerichtlinie` / `AufnahmeRL` | 2024er Fassung |
| `Aufnahmerichtlinie 2024` / `AufnahmeRL 2024` | 2024er Fassung (explizit) |
| `Aufnahmerichtlinie 2013` / `AufnahmeRL 2013` | 2013er Fassung |
| `Richtlinie (EU) 2024/1346` | 2024er Fassung |
| `Richtlinie 2013/33/EU` | 2013er Fassung |
| `Eurodac-Verordnung` / `Eurodac-VO` | 2024er Fassung |
| `Eurodac-Verordnung 2024` / `Eurodac-VO 2024` | 2024er Fassung (explizit) |
| `Eurodac-Verordnung 2013` / `Eurodac-VO 2013` | 2013er Fassung |
| `Verordnung (EU) 2024/1358` | 2024er Fassung |
| `Verordnung (EU) Nr. 603/2013` | 2013er Fassung |

## Verlinktes Format

`Art. 5 Anerkennungsverordnung` verlinkt auf die Wiki-Seite `art._5_anerkennungsverordnung`.

Für Rechtsakte im alten „(EU) Nr. NNNN/YYYY"-Format enthält der Seitenname `_nr._`, z. B. verlinkt `Art. 13 Dublin-III-Verordnung` auf `art._13_dublin-iii-verordnung`. Die Startseite des Rechtsakts lautet entsprechend `verordnung_eu_nr._604_2013`.

Für alte Richtlinien im Format „YYYY/NN/EU" steht die Jahreszahl am Anfang, z. B. `richtlinie_2013_33_eu`.

## Normen ohne Wiki-Seite

Verweise auf Gesetze, die das Wiki nicht führt, verlinkt normi extern — in dieser Reihenfolge auf die erste Seite, die das Gesetz führt:

1. **dejure.org**, z. B. `https://dejure.org/gesetze/StGB/46.html`
2. **buzer.de**, z. B. `https://www.buzer.de/s1.htm?a=8&g=RBEG`
3. **gesetze-im-internet.de** als letzte Notlösung, z. B. `https://www.gesetze-im-internet.de/vwvfg/__5.html`

Führt keine der drei Seiten das Gesetz, bleibt der Verweis reiner Text (und damit für andere Plugins zugänglich). Für Gesetze, die das Wiki selbst führt, ändert sich nichts: Sie werden weiter ins Wiki verlinkt, auch wenn die Seite des Paragraphen noch fehlt (roter Link).

**Erkannt werden:**

- **Abkürzungen** mit mindestens zwei Großbuchstaben nach `§`/`Art.`: `§ 14 BGB`, `§ 5 VwVfG`, `§ 7 SGB II`, `Art. 6 GG`, `Art. 8 EMRK`, `Art. 47 GRCh`. Kürzel, die keine Gesetze sind (Gerichte, Zeitschriften, `UAbs`, `HS` usw.), sind ausgenommen.
- **Langformen**, die in [ExternalLaws.php](ExternalLaws.php) hinterlegt sind (`PARAGRAPH_LAWS`, `ARTICLE_LAWS`): z. B. `§ 46 Absatz 2 des Strafgesetzbuches`, `§§ 47 bis 52 des Zwölften Buches Sozialgesetzbuch`, `Artikel 16a des Grundgesetzes`, `Art. 47 der Charta der Grundrechte der Europäischen Union`. Eine fehlende Langform wird dort mit Abkürzung und Beugungsformen ergänzt.

Bisher wurde etwa `§ 46 Absatz 2 Satz 2 des Strafgesetzbuches` auf einer AufenthG-Seite fälschlich auf § 46 AufenthG bezogen; jetzt zeigt der Link auf § 46 StGB.

**Prüfung und Cache:** Ob eine Seite das Gesetz führt, fragt normi einmal pro Gesetz online ab und merkt sich das Ergebnis 30 Tage lang in DokuWikis Cache-Verzeichnis (`*.normi`). dejure.org drosselt schnelle Anfragen (HTTP 429). Antwortet eine Seite nicht, gilt das Ergebnis nur eine Stunde, und die Wiki-Seite wird so lange nicht gecacht; pro Seitenaufruf werden höchstens sechs neue Gesetze (bzw. 8 Sekunden) geprüft, der Rest beim nächsten Aufruf. Unmittelbar nach dem Einspielen können externe Links deshalb vorübergehend fehlen oder auf buzer.de statt dejure.org zeigen.

## Vorschau beim Überfahren

Bleibt der Mauszeiger kurz (0,5 s) auf einem von normi erzeugten Link stehen, öffnet sich über dem Mauszeiger ein Popup mit einer Vorschau der Zielseite. Der Inhalt wird über `?do=export_xhtmlbody` geladen, also ohne Sidebar und Template. Man kann mit der Maus in das Popup wechseln, um darin zu scrollen oder Links anzuklicken; `Esc` schließt es. Die Vorschau erscheint nur bei Links auf existierende Seiten und nicht auf reinen Touch-Geräten.

## Lizenz

Copyright (C) Marcel Keienborg <marcel@aufentha.lt>

GPL v2 — https://www.gnu.org/licenses/gpl-2.0.html
