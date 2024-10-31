=== Seznam Webmaster ===
Contributors: Lukaaashek
Stable tag: 1.4.7
Tested up to: 6.6
Requires at least: 5.0
Requires PHP: 5.6.20

Integruje kód služby Seznam Webmaster a komunikuje se službou pomocí API

== Description ==

Po propojení se službou Seznam Webmaster odesílá ihned po uložení publikované stránky, příspěvky a další. 
Plugin je vyvíjen nezávisle a společnost Seznam není zodpovědná za fungování pluginu.
Jak plugin funguje najdete na [Seznam Webmaster plugin](https://lukashartmann.cz/wordpress-plugin-pro-seznam-webmaster/)

== Upozornění ==
**Aktivace Seznam Webmaster API může u nově přidaných webů trvat i více než den.** 
Ve službě Seznam Webmaster se to dozvíte v menu API->Popis a dokumentace. 
Nahoře se zobrazuje hláška a po zkušebním odeslání informace o prodlevě v aktivaci. 
Na webu tuto informaci najdete na stránce "Stav webu".
**Neaktivní API nijak neomezuje běh webu**, nechte tedy plugin aktivní a po aktivaci API se vše samo rozběhne.

== Seznam Webmaster API ==

Plugin komunikuje se službou Seznam Webmaster pomocí API v těchto případech:
1. Stahování informací o webu i jednotlivých stránkách.
2. Požadavek na reindexaci.

[Více o API v rozhraní Seznam Webmaster](https://reporter.seznam.cz/wm/)
[Ochrana údajů](https://onas.seznam.cz/cz/ochrana-udaju/)

== Installation ==

Jak plugin nainstalovat

1. Nahrajte plugin do složky /wp-content/plugins/seznam-webmaster nebo ho nainstalujte přímo přes WordPress administraci.
2. Aktivujte plugin na obrazovce "Pluginy".
3. Přejděte na obrazovku Nastavení -> Seznam Webmaster
4. Do polí zadejte hodnotu meta tagu a API klíč vygenerovanou na stránkách Seznam Webmaster.

== Frequently Asked Questions ==

= Je to zdarma? =
Ano

= Musím po nastavení ještě něco dělat? =
Ne, plugin automaticky odesílá k reindexaci publikovanou stránku/příspěvek/jiné ihned po uložení.
U nově vložených webů do Seznam Webmaster může dojít k prodlevě při aktivaci API.
Do 24 hodin by se mělo API aktivovat.

== Changelog ==

= 1.4.7 =
* Oprava warning hlášky "documents"

= 1.4.6 =
* Úprava vkládání scriptů

= 1.4.5 =
* Přidání kontroly formátu metatagu a API klíče.

= 1.4.4 =
* Přidání tlačítka skrytí logů pro zamezení nechtěného smazání logů

= 1.4.3 =
* Omezení reindexace jen na veřejné typy příspěvků a taxonomie

= 1.4.2 =
* Oprava posunutého měsíce na grafu.
* Úprava vykreslení ikřivky grafu.

= 1.4.1 =
* Fixní nula jako minimální hodnota na historickém grafu.
* Info o nulovém počtu URL v kategorii.

= 1.4.0 =
* Přidán výpis náhodného vzorku URL z jednotlivých kategorií.
* Přidán historický graf vývoje počtu stránek v jednotlivých kategoriích.

= 1.3.1 =
* Úprava kontroly aktivace nového webu ve službě Seznam Webmaster.

= 1.3.0 =
* Přidána kontrola aktivace nového webu ve službě Seznam Webmaster.

= 1.2.0 =
* Přidána hláška o prodlevě funkčnosti u nově vložených webů do služby Seznam Webmaster.

= 1.1.0 =
* Přidán výpis chybové hlášky API klíče.

= 1.0.0 =
* Vydána stabilní verze pluginu

= 0.3.0 =
* Přidán link na nastavení do menu pluginů.
* Přidána hláška o chybějícím API klíči.

= 0.2.0 =
* Administrace pluginu oddělena do samostatné záložky a rozdělena na menší části.
* Přidána kontrola existence a správnosti klíčů.
* Přidáno logování odeslaných URL.
* Vylepšené jednoduché i hromadné odesílání.

= 0.1.0 =
* Úvodní, ale funkční verze.