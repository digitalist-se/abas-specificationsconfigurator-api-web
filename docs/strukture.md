Strukture
=========

Seite -> Schritt -> Elemente

Seite
---
Eine Seite beinhaltet mehrere Schritte.

Schritte
--------

Ein Schritt enthält eine Überschrift, ein Erklärungstext und eine liste von Elementen.

Elemente
---
Ein Element ist je nach Typ unterschiedlich.

| Typ        | Beschreibung |
|:-------     |--------------|
| headline    | Übeschrift |
| content     | Normaler plaintext |
| text        | Eine Frage mit einem Freitextfeld |
| choice      | Eine Frage mit einer Auswahlmöglichkeit |
| slider      | Eine Frage mit einem Slider |

Das Auswahl element vom Typ "choice" enthält sub types.
Ein Auswahltyp kann z.B. eine ja nein frage sein.
Zusätzlich gibt es ein paar spezialfälle.

Spezialfälle:

| Auswahl Typ | Nach Paramter | Beschreibung |
|:-------     |:---           |--------------|
| business    | type          | Spezieller Branchen auswahl mit Bildern |
| lights      | type          | Ampel Auswahl |
| -           | multiple      | Eine Auswahlfrage in dem man mehrere optionen auswählen darf. |
| -           | slider        | Es wird ein Slider dargestellt. Die Auswahlmöglichkeiten werden im Slider verschleiert. |


Texte
-----

Alle Texte werden in der Datenbank mit einem Key (Schlüssel wert) hinterlegt.
z.B: key: "branche.option.einzelfertigung" -> "Einzelfertigung"
In den Konfigurationsdaten wird immer nur der Key verwendet.
Die Texte müssen in der App und in dem Word-Dokument ersetzt werden.
Die App wird über die [API](./api/text/get_list.md) die Texte beziehen.
