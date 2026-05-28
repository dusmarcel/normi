# normi Plugin für DokuWiki

Erzeugt automatisch interne Links auf Wiki-Seiten für einzelne Artikel von EU-Rechtsakten des Gemeinsamen Europäischen Asylsystems (GEAS).

## Installation

Das Plugin muss unter `lib/plugins/normi/` in der DokuWiki-Installation abgelegt werden — der Ordnername muss exakt `normi` lauten.

## Verwendung

Sobald das Plugin aktiv ist, werden Verweise auf Artikel von bekannten Rechtsakten automatisch verlinkt. Der Link zeigt stets auf die Seite des genannten Artikels.

Nach der Artikelnummer können optional Untereinheiten in folgender Reihenfolge angegeben werden: **Absatz** (`Absatz`/`Abs.`), **Unterabsatz** (`Unterabsatz`/`UA`), **Satz** (`Satz`/`S.`), **Nummer** (`Nummer`/`Nr.`) und **Buchstabe** (`lit. a)`). Alle diese Angaben sind optional; soweit sie erscheinen, müssen sie in dieser Reihenfolge stehen. Sie beeinflussen nur den Linktext, nicht das Linkziel.

**Aufzählungen** mit Komma und/oder „und" werden erkannt: Jeder genannte Artikel erhält einen eigenen Link.

**Bereichsangaben** mit „bis" werden ebenfalls erkannt: Der erste und der letzte Artikel werden je einzeln verlinkt, „bis" bleibt als Klartext erhalten.

**Beispiele:**

| Wikitext | Ergebnis |
|---|---|
| `Art. 5 Anerkennungsverordnung` | Ein Link auf Art. 5 |
| `Artikel 12a AsylverfahrensVO` | Ein Link auf Art. 12a |
| `Art. 1 f. AufnahmeRL` | Ein Link (f. beeinflusst nur den Linktext) |
| `Art. 3 ff. Verordnung (EU) 2024/1351` | Ein Link |
| `Art. 7 Abs. 1 AMMVO` | Ein Link auf Art. 7 |
| `Artikel 7 Absatz 1 Satz 2 AMMVO` | Ein Link auf Art. 7 |
| `Art. 7 Abs. 2 Nr. 3 lit. b) AMMVO` | Ein Link auf Art. 7 |
| `Art. 23, 24 AVVO` | Zwei Links (Art. 23 und 24) |
| `Art. 23 und 24 AVVO` | Zwei Links |
| `Art. 23, 24 und 25 AVVO` | Drei Links |
| `Art. 7 bis 9 Krisenverordnung` | Zwei Links (Art. 7 und Art. 9), „bis" als Klartext |

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

Bei nationalen Gesetzen können Paragraphen mit `§` (Singular) oder `§§` (Plural) zitiert werden, alternativ zu `Art.`/`Artikel`.

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
