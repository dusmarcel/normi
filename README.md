# normi Plugin für DokuWiki

Erzeugt automatisch interne Links auf Wiki-Seiten für Artikel von EU-Rechtsakten des Gemeinsamen Europäischen Asylsystems (GEAS) sowie für Paragraphen nationaler deutscher Gesetze.

## Installation

Das Plugin muss unter `lib/plugins/normi/` in der DokuWiki-Installation abgelegt werden — der Ordnername muss exakt `normi` lauten.

## Verwendung

Sobald das Plugin aktiv ist, werden Verweise auf Artikel von bekannten Rechtsakten automatisch verlinkt. Der Link zeigt stets auf die Seite des genannten Artikels.

### Präfixe

Artikel können mit folgenden Präfixen geschrieben werden: `Art.`, `Artikel`, `des Artikels` (Genitiv Singular), `Artikeln` (Dativ Plural bei Aufzählungen). Paragraphen nationaler Gesetze verwenden `§` oder `§§`.

### Untereinheiten

Nach der Artikelnummer können optional Untereinheiten in folgender Reihenfolge angegeben werden:

| Einheit | Schreibweisen |
|---|---|
| Absatz | `Absatz`, `Abs.`, `Absätze` (Plural, auch mit Nummernliste) |
| Unterabsatz | `Unterabsatz`, `UA` |
| Satz | `Satz`, `S.` |
| Nummer | `Nummer`, `Nr.` |
| Buchstabe | `Buchstabe a, b oder c`, `lit. a)` |

Alle Untereinheiten sind optional und beeinflussen nur den Linktext, nicht das Linkziel.

### Aufzählungen und Bereiche

**Aufzählungen** mit Komma und/oder „und" werden erkannt: Jeder genannte Artikel erhält einen eigenen Link.

**Bereichsangaben** mit „bis" werden ebenfalls erkannt: Der erste und der letzte Artikel werden je einzeln verlinkt, „bis" bleibt als Klartext erhalten.

### Kontextbasierte Verlinkung

Wird kein Rechtsaktname genannt, versucht das Plugin, die gemeinte Norm aus der **Überschrift der aktuellen Wiki-Seite** zu ermitteln. Das gilt für:

- **Selbstverweise** wie `der vorliegenden Verordnung`, `der vorliegenden Richtlinie`, `des vorliegenden Gesetzes`
- **Bare Artikelverweise** ohne Rechtsaktangabe, z. B. `Artikel 42 Absätze 1 und 3` oder `Artikeln 14 und 15`
- **Bare Paragraphenverweise** ohne Gesetzesangabe, z. B. `§ 25 Abs. 3` oder `§§ 16b, 16c und 17`

Enthält die Seitenüberschrift keinen bekannten Rechtsaktnamen, bleibt der Ausdruck unverlinkt.

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
| `Artikel 42 Absätze 1 und 3 AVVO` | Ein Link auf Art. 42 |
| `Art. 42 Absatz 3 Buchstabe a, b oder c AVVO` | Ein Link auf Art. 42 |
| `Artikel 39 Absatz 3 der Verordnung (EU) 2024/1348` | Ein Link auf Art. 39 |
| `des Artikels 8 Absatz 7 der vorliegenden Richtlinie` | Ein Link (Norm aus Seitentitel) |
| `Art. 23, 24 AVVO` | Zwei Links (Art. 23 und 24) |
| `Art. 23, 24 und 25 AVVO` | Drei Links |
| `Art. 7 bis 9 Krisenverordnung` | Zwei Links (Art. 7 und Art. 9), „bis" als Klartext |
| `Artikel 42 Absätze 1 und 3` | Ein Link (Norm aus Seitentitel) |
| `Artikeln 14 und 15` | Zwei Links (Norm aus Seitentitel) |

## Unterstützte Rechtsakte

### GEAS-Reform 2024

| Rechtsakt | Synonyme |
|---|---|
| Anerkennungsverordnung (EU) 2024/1347 | Qualifikationsverordnung, AnerkennungsVO, QualifikationsVO, QVO, Statusverordnung, StatusVO |
| Asylverfahrensverordnung (EU) 2024/1348 | AsylverfahrensVO, AVVO |
| Aufnahmerichtlinie (EU) 2024/1346 | AufnahmeRL, Aufnahmerichtlinie 2024, AufnahmeRL 2024 |
| Grenzrückführungsverordnung (EU) 2024/1349 | GrenzrückführungsVO, GrenzRüFüVO, GrenzRFVO |
| Resettlementverordnung (EU) 2024/1350 | ResettlementVO |
| Asyl- und Migrationsmanagement-Verordnung (EU) 2024/1351 | AMM-VO, AMMVO |
| Screening-Konsistenz-Verordnung (EU) 2024/1352 | Screening-Konsistenz-VO |
| Screening-Verordnung (EU) 2024/1356 | Screening-VO |
| Eurodac-Verordnung (EU) 2024/1358 | Eurodac-VO, Eurodac-Verordnung 2024, Eurodac-VO 2024 |
| Krisenverordnung (EU) 2024/1359 | KrisenVO |
| EUAA-Verordnung (EU) 2021/2303 | EUAA-VO |

### Ältere Rechtsakte (GEAS-System bis 2024)

| Rechtsakt | Synonyme |
|---|---|
| Aufnahmerichtlinie 2013/33/EU | Aufnahmerichtlinie 2013, AufnahmeRL 2013 |
| Qualifikationsrichtlinie 2011/95/EU | QualifikationsRL, Anerkennungsrichtlinie, AnerkennungsRL |
| Asylverfahrensrichtlinie 2013/32/EU | AsylverfahrensRL |
| Dublin-III-Verordnung (EU) Nr. 604/2013 | Dublin-III-VO, DublinVO, Dublin III, Dublin-III, Dublin III-Verordnung, Dublin III-VO |
| Eurodac-Verordnung (EU) Nr. 603/2013 | Eurodac-Verordnung 2013, Eurodac-VO 2013 |
| EASO-Verordnung (EU) Nr. 439/2010 | EASO-VO |

Alle Synonyme und EU-Nummern eines Rechtsakts verlinken auf dieselbe Zielseite.

### Nationale Gesetze (Deutschland)

Bei nationalen Gesetzen werden Paragraphen mit `§` (Singular) oder `§§` (Plural) zitiert.

| Gesetz | Abkürzung |
|---|---|
| Aufenthaltsgesetz | AufenthG |
| Asylgesetz | AsylG |

**Beispiele:**

| Wikitext | Ergebnis |
|---|---|
| `§ 25 AufenthG` | Ein Link auf § 25 |
| `§ 25 Abs. 1 AufenthG` | Ein Link auf § 25 |
| `§§ 25, 26 AufenthG` | Zwei Links |
| `§§ 25 bis 27 AufenthG` | Zwei Links (§ 25 und § 27), „bis" als Klartext |
| `§ 25 Abs. 3` | Ein Link (Norm aus Seitentitel) |
| `§§ 16b, 16c, 16e und 17` | Vier Links (Norm aus Seitentitel) |

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

## Lizenz

Copyright (C) Marcel Keienborg <marcel@aufentha.lt>

GPL v2 — https://www.gnu.org/licenses/gpl-2.0.html
