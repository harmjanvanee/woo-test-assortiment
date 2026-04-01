=== WooCommerce Test Assortiment ===
Contributors: harmjanvanee
Tags: woocommerce, cart, trial, products
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.8.9
License: GPLv2 or later

Hiermee kunnen klanten een test-variant van een product toevoegen aan hun winkelwagen en ontvangen ze een kortingscode.

== Description ==

Deze plugin voegt een "Probeerbox" functionaliteit toe aan WooCommerce, waarbij klanten proefmonsters kunnen selecteren en toevoegen aan hun winkelwagen.

== Changelog ==

= 1.8.9 =
* UI: Implemented "De Bonus Focus" layout for the sticky bar.
* UI: Added a green "gratis shoptegoed" badge to highlight the future credit value.
* UX: Improved visual hierarchy with stacked information (products, total price, bonus credit).
* Refinement: Updated button text to "Toevoegen aan winkelwagen" for better clarity.

= 1.8.8 =
* Improvement: Clearer distinction in sticky bar between current collection total ("Totaal nu te betalen") and future credit value ("Waarde tegoedbon").
* UI: Updated sticky bar layout to use a two-row pricing display for better clarity.
* Logic: Pricing in sticky bar now correctly reflects the actual total to be paid and the future coupon value.

= 1.8.7 =
* Feature: Added action price and credit label to the sticky bar.
* Fix: Improved alignment of price and label on product cards to keep them on the same line.
* Improvement: Dynamic discount percentage now localized for frontend JS calculations.

= 1.8.6 =
* Feature: Dynamisch instelbaar kortingspercentage (nu standaard op 50%).
* UI: Productkaarten uitgebreid met actieprijs, doorgestreepte originele prijs en 'Na verrekening tegoed' label.
* Fix: Couponbedrag is nu ook gekoppeld aan het dynamische kortingspercentage.

= 1.8.5 =
* Fix: Coupons worden nu alleen gegenereerd voor producten die specifiek via de Probeerbox-flow zijn toegevoegd.
* Fix: Voorkomt onbedoelde korting bij reguliere aankopen van dezelfde producten.

= 1.8.4 =
* Toegevoegd: Changelog ondersteuning via readme.txt.
* Opgelost: Versie mismatch in plugin header.

= 1.8.3 =
* Toegevoegd: Multi-select filtering voor subcategorieën.
* Verbetering: Minimalistische styling voor het filtermenu.
* Interface: Skeleton loading voor betere gebruikerservaring.

= 1.8.2 =
* Initiële integratie van GitHub update systeem.
* Basis categorie filtering.
