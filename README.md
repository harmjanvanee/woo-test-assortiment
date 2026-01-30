# WooCommerce Test Assortiment

## Doel van de Plugin
De **WooCommerce Test Assortiment** plugin is ontworpen om de conversie te verhogen door klanten de mogelijkheid te bieden om "test-verpakkingen" of "proefmonsters" van producten te bestellen in een overzichtelijke interface. 

Het hoofddoel is om een drempelverlagende "Kies & Mix" ervaring te creëren (de **Probeerbox**), waarbij klanten meerdere test-varianten kunnen selecteren en deze in één keer gegroepeerd aan hun winkelwagen kunnen toevoegen.

---

## Kernfunctionaliteiten

### 1. Probeerbox Groepering
- Klanten kunnen in de instellingen een "Hoofdproduct" (de Probeerbox) aanwijzen.
- Alle geselecteerde test-varianten worden in de winkelwagen gekoppeld aan dit hoofdproduct.
- **Slimme Logica**: Als het hoofdproduct uit de winkelwagen wordt verwijderd, worden automatisch alle bijbehorende test-producten ook verwijderd.
- **Visuele Hiërarchie**: In de winkelwagen worden de sub-producten ingesprongen getoond onder de Probeerbox.

### 2. Dynamisch Test-Assortiment (Shortcode)
- Gebruik `[test_assortiment_grid]` om een grid van producten te tonen uit een specifieke categorie.
- De plugin zoekt automatisch naar een variatie die het woord "test" in de naam heeft (geconfigureerd via de backend).
- **Interactieve Card**:
  - 3:2 Landscape afbeeldingen met hover-overlay.
  - Directe selectie-modus (multi-select).
  - Toont alleen de unieke variantnaam (bijv. "Klein") om herhaling te voorkomen.

### 3. Selectie Persistentie
- Gebruikerselecties worden opgeslagen in de `localStorage` van de browser.
- Als een klant de pagina ververst of later terugkomt, blijven de geselecteerde producten in het grid bewaard.

### 4. Responsieve UI/UX
- Volledig responsive grid (mobile first).
- "Sticky Bar" onderaan het scherm die de voortgang van de selectie toont en een snelle "Bestellen" knop bevat.

---

## Technische Architectuur

De plugin is opgebouwd volgens een modulaire structuur voor eenvoudiger onderhoud:

- `woo-test-assortiment.php`: Hoofdbestand, regelt de initialisatie en enqueuing van assets.
- `includes/`:
    - `class-shortcodes.php`: Verwerkt de `[test_assortiment_grid]` en andere shortcodes. Bevat de HTML-template voor de productkaarten.
    - `class-cart-manager.php`: Bevat alle logica voor de winkelwagen, inclusief de groeperings-omgeving en AJAX handlers.
    - `class-settings-page.php`: Regelt de instellingenpagina in de WooCommerce backend.
    - `class-product-helper.php`: Helper functies voor het ophalen van variaties en categorieën.
- `assets/`:
    - `css/frontend.css`: De styling van het grid, de cards en de winkelwagen-groepering.
    - `js/frontend.js`: Regelt de multi-select logica, localStorage en AJAX calls.

---

## Ontwikkeling & Onderhoud

### Database Velden
De plugin gebruikt de standaard WordPress `options` tabel:
- `wta_assortment_category`: De geselecteerde categorie voor het grid.
- `wta_probeerbox_id`: Het ID van het hoofdproduct in de winkelwagen.
- `wta_cart_behavior`: Bepaalt wat er gebeurt als een product dubbel wordt toegevoegd (blokkeren of vervangen).

### Belangrijke Filters/Hooks
- `woocommerce_cart_item_class`: Gebruikt om `.wta-parent-item` en `.wta-child-item` klassen toe te voegen voor styling.
- `woocommerce_cart_item_name`: Gebruikt om de inspringing (`↳`) toe te voegen in de winkelwagen.

### Toekomstige Verbeteringen
Mocht je de plugin willen uitbreiden, let dan op:
1. **Winkelwagen Logica**: Kijk in `class-cart-manager.php` naar de `wta_parent_key` in de `cart_item_data`. Dit is de lijm die de producten bij elkaar houdt.
2. **Styling**: De CSS gebruikt CSS Variables (`:root`) voor kleuren zoals `--wta-hookers-green`. Pas deze aan om de hele plugin in één keer te restylen.
3. **Responsive**: De grid gebruikt `auto-fill` met `minmax(280px, 1fr)`. Dit zorgt ervoor dat het grid zichzelf vult zonder media queries.

---

## Installatie
1. Upload de folder `woo-test-assortiment` naar `/wp-content/plugins/`.
2. Activeer de plugin in WordPress.
3. Ga naar **WooCommerce > Instellingen > Test Assortiment** om de juiste categorie en het Probeerbox-hoofdproduct in te stellen.
4. Plaats de shortcode `[test_assortiment_grid]` op een pagina naar keuze.
