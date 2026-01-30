# WooCommerce Test Assortiment

Deze plugin maakt het mogelijk om klanten direct een "test-variant" (de kleinste verpakking) van een product te laten toevoegen aan hun winkelwagen vanaf de assortimentpagina. Na aankoop ontvangen ze automatisch een kortingscode ter waarde van 50% van de test-varianten.

## Installatie

1. Upload de map `woo-test-assortiment` naar de `/wp-content/plugins/` directory.
2. Activeer de plugin via het WordPress 'Plugins' menu.
3. Ga naar **WooCommerce > Instellingen > Test Assortiment** om de plugin te configureren.

## Configuratie

### Identificatie Test-variant
De plugin moet weten welke variatie de "test-variant" is. Je kunt drie methodes kiezen:
- **Variatie Attribuut**: Gebruik een specifiek attribuut (bv. `pa_test-variant`) met een specifieke waarde (bv. `yes`).
- **Variatie Meta Key**: Gebruik een post meta key (bv. `_is_test_variant`) met de waarde `yes`.
- **Fallback**: Automatisch de kleinste variatie bepalen op basis van een numeriek attribuut (bv. `pa_inhoud-ml`).

### Winkelwagen Gedrag
- **Block**: Voorkom dat klanten meerdere test-verpakkingen van hetzelfde product toevoegen. Toont een foutmelding.
- **Replace**: Als er al een variant van hetzelfde product in de cart zit, wordt deze vervangen door de test-variant.

## Producten Filteren op de Webshop
Om alleen producten met een test-variant te tonen in je loopraster, raden we aan:
1. Maak een WooCommerce categorie aan genaamd **"Probeer-assortiment"**.
2. Voeg de **Parent Producten** die een test-variant hebben toe aan deze categorie.
3. In Elementor stel je je loop-raster in om alleen producten uit deze categorie te tonen.

De plugin zorgt er vervolgens voor dat de "Probeer" knop alleen verschijnt bij de producten die daadwerkelijk een variatie hebben die als test-variant is gemarkeerd.

### Coupon Instellingen
- **Trigger**: Wanneer de coupon wordt gegenereerd (bij status 'Verwerken' of 'Afgerond').
- **Bedrag**: 50% van de som van alle test-varianten in de order (incl. of excl. BTW).
- **Geldigheid**: Aantal dagen dat de code geldig is.
- **Beperkingen**: De code is gekoppeld aan het e-mailadres van de koper en is slechts 1x bruikbaar.

## Gebruik in Elementor / Shortcodes

### Shortcodes
- `[test_add_button label="Probeer"]`: Toont een AJAX-knop. Het `product_id` wordt automatisch herkend in een (WooCommerce) loop. Je kunt ook handmatig `product_id="123"` toevoegen.
- `[test_coupon_from_order]`: Toont de gegenereerde couponcode op een bedankpagina.

### Elementor Widget
Zoek naar de widget **Test Add Button** in Elementor. Je kunt hier het label aanpassen en optioneel een specifiek Product ID opgeven (handig in custom loops).

## Debugging
Schakel 'Debug Mode' in de instellingen in om logs te bekijken via **WooCommerce > Status > Logs**.
