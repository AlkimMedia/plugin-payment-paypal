# User-Guide PayPal Plugin

Das plentymarkets PayPal Plugin bietet Ihnen die Möglichkeit, **PayPal**, **PayPal PLUS** und **Ratenzahlung powered by PayPal** in Ihren Webshop einzubinden.

## Einrichtung

Von der Installation des Plugins bis zur vollen Funktionalität sind es nur einige wenige Schritte.

### PayPal konfigurieren

Bevor Sie die Funktionen des PayPal Plugins nutzen können, müssen Sie zuerst Ihr Konto mit ihrem plentymarkets System verbinden.

#### Ein PayPal-Konto in plentymarkets einrichten

Sie benötigen ein aktuelles PayPal-Konto. Sollten Sie noch nicht über ein Konto verfügen, können Sie <strong><a href="https://www.paypal.com/de/webapps/mpp/merchant" target="_blank">hier</a></strong> eines eröffnen.

##### Ein PayPal-Konto in plentymarkets hinzufügen:
  
1. Öffnen Sie das Menü **Einstellungen » Aufträge » PayPal » Kontoeinstellungen**.
2. Klicken Sie auf **Ein neues PayPal-Konto hinzufügen**.
3. Geben Sie eine Email-Adresse ein.  
	→ Diese Email-Adresse dient gleichzeitig als der Name des Kontos.
4. Geben Sie Mandanten-ID und Mandanten-Geheimwort ein.
5. Klicken Sie auf **Hinzufügen**.  
	→ Das Konto erhält einen eigenen Eintrag.

Sobald Ihr Konto hinzugefügt ist, können Sie mit einem Klick darauf weitere Einstellungen vornehmen.

##### Ein PayPal-Konto in plentymarkets verwalten
 
1. Öffnen Sie das Menü **Einstellungen » Aufträge » PayPal » Kontoeinstellungen**.
2. Klicken Sie auf das Konto, das Sie konfigurieren möchten.  
	→ Das Konto wird geöffnet.
3. Nehmen Sie die Einstellungen wie in der Tabelle beschrieben vor.
4. **Speichern** Sie die Einstellungen.

<table>
	<thead>
		<th>
			Einstellung
		</th>
		<th>
			Erläuterung
		</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<b>Konto</b>
			</td>
			<td>Die eingegebene Email-Adresse, die gleichzeitig als Name des Kontos fungiert. Diese Angabe kann nicht mehr geändert werden.</td>
		</tr>
		<tr>
			<td>
				<b>Mandanten-ID</b>
			</td>
			<td>Ihre ID. Diese Angabe kann nicht mehr geändert werden.</td>
		</tr>
		<tr>
			<td>
				<b>Mandanten-Geheimwort</b>
			</td>
			<td>
				Ihr Geheimwort. Diese Angabe kann nicht mehr geändert werden.		</td>
		</tr>
		<tr>
			<td>
				<b>Umgebung</b>
			</td>
			<td>
				Stellen Sie zwischen Testumgebung und Live-Umgebung um. Für die Testumgebung empfiehlt sich ein Testkonto mit Testnutzer in der <strong><a href="https://developer.paypal.com/developer/accounts/" target="_blank">PayPal Sandbox</a></strong>. Sobald Sie auf Live-Umgebung umschalten, können Ihre Daten von Kunden für Zahlungen verwendet werden.
			</td>
		</tr>
		<tr>
			<td>
				<b>Logo-URL</b>
			</td>
			<td>
			Eine https-URL, die zu Ihrem Logo-Bild führt. Verwenden Sie ein gültiges Format wie .gif, .jpg oder .png. Die Maximalgröße beträgt 190 Pixel in der Breite und 60 Pixel in der Höhe. PayPal schneidet größere Bilder ab. PayPal platziert Ihr Logo ganz oben in der Warenkorbübersicht.
			</td>
		</tr>
		<tr>
			<td>
				<b>Warenkorbumrandung</b>
			</td>
			<td>
				Der hexadezimale HTML-Code Ihrer Haupterkennungsfarbe. PayPal lässt diese Farbe in einem Rahmen um die Warenkorbübersicht in der PayPal Checkout-Benutzeroberfläche stufenlos zu weiß verlaufen.
			</td>
		</tr>
		<tr>
			<td>
				<b>Zeitpunkt PayPal-Zahlungseinzug</b>
			</td>
			<td>
				<strong>Sale (sofortiger Zahlungseinzug)</strong> – Zahlung direkt nach Abschluss der Bestellung einziehen.
			</td>
		</tr>
	</tbody>
</table>

### Zahlungsarten verwalten

In diesem Abschnitt erfahren Sie, wie Sie die verschiedenen von PayPal angebotenen Zahlungsarten in Ihrem Webshop anbieten.

#### PayPal / PayPal PLUS

Sobald Sie das PayPal Plugin installiert und Ihr Konto eingerichtet haben, ist PayPal ohne weitere Einstellungen als Zahlungsart verfügbar. Es erscheint im Checkout je nach Priorität neben den anderen eingestellten Zahlungsarten. 

Im Menü **Einstellungen » Aufträge » PayPal » PayPal / PayPal PLUS** haben Sie die folgenden Einstellmöglichkeiten:
 
<table>
	<thead>
		<th>
			Einstellung
		</th>
		<th>
			Erläuterung
		</th>
	</thead>
	<tbody>
		<tr>
		<td class="th" align=CENTER colspan="2">Allgemein</td>
		</tr>
		<tr>
			<td>
				<b>Aktives Konto</b>
			</td>
			<td>Das Konto, für das Sie die Einstellungen vornehmen. Dieses muss für jeden in der linken Spalte auswählbaren Mandanten einzeln eingestellt werden. Es ist möglich, ein Konto für mehrere Mandanten zu verwenden.</td>
		</tr>
		<tr>
			<td>
				<b>Priorität</b>
			</td>
			<td>Bestimmt, an welcher Stelle PayPal im Checkout steht, solange Sie nicht PayPal PLUS verwenden.</td>
		</tr>
		<tr>
		<td class="th" align=CENTER  colspan="2">Spezifische Bewerbung</td>
		</tr>
		<tr>
			<td>
				<b>Aktivieren</b>
			</td>
			<td>
				Aktiviert die PayPal PLUS Wall im Checkout. Sie gibt Ihren Kunden die Möglichkeit, ihren Einkauf mit Deutschlands beliebtesten Zahlungsarten PayPal, Lastschrift, Kreditkarte sowie Kauf auf Rechnung zu bezahlen – sogar, wenn diese nicht über ein PayPal-Konto verfügen.			</td>
		</tr>
		<tr>
		<td class="th" align=CENTER colspan="2">Spracheinstellungen</td>
		</tr>
		<tr>
			<td>
				<b>Sprache</b>
			</td>
			<td>
				Hinterlegen Sie die Sprachpakete für alle Sprachen, in denen Ihr Shop zugänglich ist. Stellt ein Kunde den Webshop auf eine andere Sprache um, greift PayPal auf diese Sprachpakete zu.
			</td>
		</tr>
		<tr>
			<td>
				<b>Infoseite</b>
			</td>
			<td>
				Als Information zur Zahlungsart eine Kategorieseite vom Typ <strong>Content</strong> anlegen oder die URL einer Webseite eingeben.
			</td>
		</tr>
		<tr>
			<td>
				<b>Anzeigename</b>
			</td>
			<td>
				Die Bezeichnung, die im Checkout für die Zahlung mit PayPal angezeigt wird.
			</td>
		</tr>
		<tr>
			<td>
				<b>Logo</b>
			</td>
			<td>
			Eine https-URL, die zu Ihrem Logo-Bild führt. Verwenden Sie ein gültiges Format wie .gif, .jpg oder .png. Die Maximalgröße beträgt 190 Pixel in der Breite und 60 Pixel in der Höhe. PayPal schneidet größere Bilder ab. PayPal platziert Ihr Logo ganz oben in der Warenkorbübersicht.
			</td>
		</tr>
		<tr>
		<td class="th" colspan="2"></td>
		</tr>
		<tr>
			<td>
				<b>Lieferländer</b>
			</td>
			<td>
				Geben Sie Ihre eingestellten Lieferländer für die Zahlung mit PayPal frei.
			</td>
		</tr>
		<tr>
			<td>
				<b>Aufpreis Webshop</b>
			</td>
			<td>
					Stellen Sie ein, ob sie für Zahlungen per PayPal im In- oder Ausland einen Aufpreis veranschlagen. Sie haben jeweils die Wahl zwischen einem pauschalen Wert und einer Prozentangabe.	</td>
		</tr>
	</tbody>
</table>

#### Ratenzahlung powered by PayPal

Im Menü **Einstellungen » Aufträge » PayPal » Ratenzahlung powered by PayPal** haben Sie die folgenden Einstellmöglichkeiten. Wenn Sie bereits Einstellungen unter **PayPal / PayPal PLUS** vorgenommen haben, können Sie diese hier übernehmen; die einzige Neuerung hier ist die Schaltfläche **Ratenkauf**, die **Aktivieren** ersetzt.

<table>
	<thead>
		<th>
			Einstellung
		</th>
		<th>
			Erläuterung
		</th>
	</thead>
	<tbody>
		<tr>
		<td class="th" align=CENTER colspan="2">Allgemein</td>
		</tr>
		<tr>
			<td>
				<b>Aktives Konto</b>
			</td>
			<td>Das Konto, für das Sie die Einstellungen vornehmen. Dieses muss für jeden Mandanten einzeln eingestellt werden.</td>
		</tr>
		<tr>
			<td>
				<b>Priorität</b>
			</td>
			<td>Bestimmt, an welcher Stelle PayPal im Checkout steht, solange Sie nicht PayPal PLUS verwenden.</td>
		</tr>
		<tr>
		<td class="th" align=CENTER colspan="2">Spezifische Bewerbung</td>
		</tr>
		<tr>
			<td>
				<b>Ratenkauf</b>
			</td>
			<td>
				Berechnet im Hintergrund die Konditionen für den Ratenkauf. Je nach verknüpften Containern kann dies z.B. der Kauf des aktiven Artikels bzw. des Inhalts des Warenkorbs sein. Ist hier kein Haken gesetzt, wird nur die allgemeine Möglichkeit der Ratenzahlung beworben.			</td>
		</tr>
		<tr>
		<td class="th" align=CENTER colspan="2">Spracheinstellungen</td>
		</tr>
		<tr>
			<td>
				<b>Sprache</b>
			</td>
			<td>
				Hinterlegen Sie die Sprachpakete für alle Sprachen, in denen Ihr Shop zugänglich ist. Stellt ein Kunde den Webshop auf eine andere Sprache um, greift PayPal auf diese Sprachpakete zu.
			</td>
		</tr>
		<tr>
			<td>
				<b>Infoseite</b>
			</td>
			<td>
				Als Information zur Zahlungsart eine Kategorieseite vom Typ <strong>Content</strong> anlegen oder die URL einer Webseite eingeben.
			</td>
		</tr>
		<tr>
			<td>
				<b>Anzeigename</b>
			</td>
			<td>
				Die Bezeichnung, die im Checkout für Ratenzahlung powered by PayPal angezeigt wird.
			</td>
		</tr>
		<tr>
			<td>
				<b>Logo</b>
			</td>
			<td>
				Eine https-URL, die zu Ihrem Logo-Bild führt. Verwenden Sie ein gültiges Format wie .gif, .jpg oder .png. Die Maximalgröße beträgt 190 Pixel in der Breite und 60 Pixel in der Höhe. PayPal schneidet größere Bilder ab. PayPal platziert Ihr Logo ganz oben in der Warenkorbübersicht.
			</td>
		</tr>
		<tr>
		<td class="th" colspan="2"></td>
		</tr>
		<tr>
			<td>
				<b>Lieferländer</b>
			</td>
			<td>
				Geben Sie Ihre eingestellten Lieferländer für die Zahlung mit Ratenzahlung powered by PayPal frei.
			</td>
		</tr>
		<tr>
			<td>
				<b>Aufpreis Webshop</b>
			</td>
			<td>
Stellen Sie ein, ob sie für Zahlungen per Ratenzahlung powered by PayPal im In- oder Ausland einen Aufpreis veranschlagen. Sie haben jeweils die Wahl zwischen einem pauschalen Wert und einer Prozentangabe.
			</td>
		</tr>
	</tbody>
</table>

## Verknüpfungen

Für die Funktionen PayPal PLUS, Ratenzahlung powered by PayPal sowie den Express button stehen Ihnen verschiedene Möglichkeiten zur Verfügung, wie Sie diese mit den Containern Ihres Shops verknüpfen können.

##### PayPal Express button
Der Express Button ist universell hinterlegbar, z.B. auf der Artikelseite (Single item) oder neben dem Warenkorb (Shopping cart). Dies gibt dem Kunden die Möglichkeit, den Artikel oder den Inhalt des gesamten Warenkorbs sofort zu kaufen, ohne den Umweg über den Checkout zu machen. Hierbei wird er direkt zur Zahlung weitergeleitet, die Lieferadresse wird von PayPal bereitgestellt.

##### PayPal PLUS Wall
Durch die Verknüpfung im Container **Checkout Override payment method** ersetzt die PayPal PLUS Wall alle vorher eingestellten Zahlungsarten. Jene Zahlungsarten, die zusätzlich zu PayPal, Lastschrift, Kreditkarte sowie Kauf auf Rechnung eingestellt sind, werden unter diesen gemäß ihrer Priorität in der Wall angezeigt.

##### PayPal Installment Generic Promotion
Stellt die allgemeine Bewerbung von Ratenzahlung powered by PayPal zur Verfügung. Stellen Sie Verknüpfungen zu allen Containern her, in denen Sie darauf hinweisen wollen, dass Sie Ratenzahlung powered by PayPal anbieten.

##### PayPal Installment Specific Promotion
Im Gegensatz zur Generic Promotion berechnet dieser Container eine Auswahl von Ratenzahlungsmöglichkeiten für den aktuellen Artikel bzw. den Inhalt des Warenkorbs. Dabei wird die günstigste Möglichkeit – gemessen am Gesamtwert – auf dem Button angezeigt. 

##### PayPal Installment Financing Check
Erstellt den Button „Ratenzahlung beantragen“ unter der Bewerbung der Ratenzahlung. Bei einem Klick darauf gelangt der Kunde auf die Webseite von Paypal zur Auswahl der Ratenzahlungsbedingungen. Im Anschluss gelangt er zurück zum Shop, da Sie als Händler noch einmal alle Details darstellen müssen, bevor der Kunde den Kauf per Ratenzahlung bestätigt. Achten Sie darauf, den Financing Check nicht als Override zu verknüpfen, da Sie ansonsten den **Jetzt Kaufen** Button auch für alle anderen Zahlungsarten ersetzen.

##### PayPal Installment Financing Costs
Nach geltendem Recht müssen bei Kaufabschluss die Finanzierungskosten dargestellt werden. Ideal ist eine Verknüpfung mit **Order confirmation after totals**, da die Finanzierungskosten unter dem Bruttogesamtbetrag dargestellt werden müssen.

##### PayPal Installment Prepare Button
Mit **Replace Place order button** wird die Schaltfläche **Jetzt kaufen** durch **Ratenzahlung beantragen** ersetzt, sobald die Zahlungsart Ratenzahlung powered by PayPal gewählt ist.