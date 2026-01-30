# Testplan: WooCommerce Test Assortiment

## Scenario 1: Identificatie Test-variant
- [ ] **Methode A (Attribute)**: Maak een product aan met variaties. Geef 1 variatie het attribuut `test_variant=yes`. Controleer of de shortcode deze variant toevoegt.
- [ ] **Methode B (Meta)**: Gebruik een plugin als ACF of handmatige meta om `_is_test_variant=yes` op 1 variatie te zetten. Controleer herkenning.
- [ ] **Methode C (Fallback)**: Stel methode C in op attribuut `pa_gewicht`. Controleer of de variant met de laagste numerieke waarde wordt gekozen.

## Scenario 2: Winkelwagen Gedrag
- [ ] **Blokkeren**: Voeg een normale variatie toe aan cart. Klik op "Probeer". Controleer of de foutmelding verschijnt en de cart niet verandert.
- [ ] **Vervangen**: Stel gedrag in op 'Replace'. Voeg een normale variatie toe. Klik op "Probeer". Controleer of de normale variatie uit de cart verdwijnt en de test-variant verschijnt.
- [ ] **AJAX**: Controleer of de mini-cart (cart fragments) direct update na een succesvolle toevoeging.

## Scenario 3: Coupon Generatie
- [ ] **Berekening**: Plaats een bestelling met 2 verschillende test-verpakkingen (totaal €20). Rond de bestelling af. Controleer of er een coupon van €10 is aangemaakt.
- [ ] **Status Trigger**: Stel trigger in op 'Completed'. Controleer dat de coupon PAS wordt aangemaakt als de order op 'Afgerond' wordt gezet.
- [ ] **E-mail**: Controleer of de couponcode in de order-bevestiging e-mail staat.
- [ ] **Bedankpagina**: Controleer of de couponcode zichtbaar is op de 'Thank You' pagina (zowel via shortcode als hook).

## Scenario 4: Beperkingen & Security
- [ ] **Gebruikslimiet**: Probeer de coupon 2x te gebruiken. De tweede keer moet WooCommerce weigeren.
- [ ] **E-mail restrictie**: Probeer de coupon te gebruiken met een ander e-mailadres dan in de oorspronkelijke order. Dit moet geblokkeerd worden.
- [ ] **Security**: Controleer of ongeautoriseerde AJAX requests (zonder nonce) worden afgewezen.
- [ ] **Guest Checkout**: Test de hele flow als niet-ingelogde klant. De coupon moet nog steeds gegenereerd worden en gekoppeld zijn aan het e-mailadres.

## Scenario 5: Elementor Integratie
- [ ] **Widget**: Voeg de widget toe aan een Elementor Archive template. Controleer of de knop dynamisch het juiste Product ID pakt in de loop.
