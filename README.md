# normi Plugin für DokuWiki

Erzeugt automatisch interne Links auf Wiki-Seiten für einzelne Artikel von EU-Rechtsakten des Gemeinsamen Europäischen Asylsystems (GEAS).

## Installation

Das Plugin muss unter `lib/plugins/normi/` in der DokuWiki-Installation abgelegt werden — der Ordnername muss exakt `normi` lauten.

## Verwendung

Sobald das Plugin aktiv ist, werden Verweise auf Artikel von bekannten Rechtsakten automatisch verlinkt. Der Link zeigt stets auf die Seite des genannten Artikels.

Nach der Artikelnummer können optional Untereinheiten in folgender Reihenfolge angegeben werden: **Absatz** (`Absatz`/`Abs.`), **Unterabsatz** (`Unterabsatz`/`UA`), **Satz** (`Satz`/`S.`), **Nummer** (`Nummer`/`Nr.`) und **Buchstabe** (`lit. a)`). Alle diese Angaben sind optional; soweit sie erscheinen, müssen sie in dieser Reihenfolge stehen. Sie beeinflussen nur den Linktext, nicht das Linkziel.

**Beispiele:**

```
Art. 5 Anerkennungsverordnung
Artikel 12a AsylverfahrensVO
Art. 1 f. AufnahmeRL
Art. 3 ff. Verordnung (EU) 2024/1351
Art. 7 bis 9 Krisenverordnung
Art. 7 Abs. 1 AMMVO
Artikel 7 Absatz 1 AMMVO
Artikel 7 Absatz 1 Satz 2 AMMVO
Art. 7 Abs. 2 Nr. 3 lit. b) AMMVO
```

## Unterstützte Rechtsakte

| Rechtsakt | Synonyme |
|---|---|
| Anerkennungsverordnung (EU) 2024/1347 | Qualifikationsverordnung, AnerkennungsVO, QualifikationsVO, QVO, Statusverordnung, StatusVO |
| Asylverfahrensverordnung (EU) 2024/1348 | AsylverfahrensVO |
| Aufnahmerichtlinie (EU) 2024/1346 | AufnahmeRL |
| Grenzrückführungsverordnung (EU) 2024/1349 | GrenzrückführungsVO, GrenzRüFüVO, GrenzRFVO |
| Resettlementverordnung (EU) 2024/1350 | ResettlementVO |
| Asyl- und Migrationsmanagement-Verordnung (EU) 2024/1351 | AMM-VO, AMMVO |
| Screening-Konsistenz-Verordnung (EU) 2024/1352 | Screening-Konsistenz-VO |
| Screening-Verordnung (EU) 2024/1356 | Screening-VO |
| Eurodac-Verordnung (EU) 2024/1358 | Eurodac-VO |
| Krisenverordnung (EU) 2024/1359 | KrisenVO |
| EUAA-Verordnung (EU) 2021/2303 | EUAA-VO |

Alle Synonyme und EU-Nummern eines Rechtsakts verlinken auf dieselbe Zielseite.

## Verlinktes Format

`Art. 5 Anerkennungsverordnung` verlinkt auf die Wiki-Seite `art._5_anerkennungsverordnung`.

## Lizenz

Copyright (C) Marcel Keienborg <marcel@aufentha.lt>

GPL v2 — https://www.gnu.org/licenses/gpl-2.0.html
