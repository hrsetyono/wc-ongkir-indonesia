# Ongkos Kirim Indonesia for WooCommerce

![](https://raw.github.com/hrsetyono/cdn/master/woocommerce-indo-shipping/ongkir-banner.jpg)

Calculate the shipping costs for Indonesian couriers in WooCommerce.

> This plugin requires PRO License from RajaOngkir.com. We are not affiliated with them in any way.

This plugin is free and provided as is. If you found a bug, please submit it [here](https://github.com/hrsetyono/woocommerce-indo-shipping/issues).

**Supported Couriers:**

- JNE
- TIKI
- Pos Indonesia
- J&T
- SiCepat
- Ninja Express
- AnterAja

**Tested working on:**

- PHP 7.3 with `CURL` and `ALLOW_URL_FOPEN` enabled.
- WooCommerce 5.9.0
- WordPress 5.8.3
- Storefront theme and [Edje Theme](https://github.com/hrsetyono/edje-wp-theme)

## 1. How to Install

1. Download our latest version at https://github.com/hrsetyono/wc-ongkir-indonesia/releases/

1. Go to your WP Admin panel > Plugins > Add New

1. Choose to upload your own zip file.

**Alternative Way using Composer**:

This plugin is available in Composer under this name:

    "pixelstudio/wc-ongkir-indonesia": "~2.3.0"

If you don't know how to use Composer to manage plugins, visit here https://wptips.dev/composer-to-manage-plugins/

## 2. Initial Setup

![](https://raw.github.com/hrsetyono/cdn/master/woocommerce-indo-shipping/ongkir-setup.jpg)

1. Go to WooCommerce > Settings > Shipping > Indo Shipping.

1. Enter RajaOngkir PRO API Key and press "Save". If it's correct, it will say "Connected" and the rest of the fields will appear.

1. Tick Enable.

1. Set your shop's city / district location. It is based on the Province you set in General setting.

1. Select the service you want to enable from each courier. Leave empty if you want to disable it.

![](https://raw.github.com/hrsetyono/cdn/master/woocommerce-indo-shipping/ongkir-zone.jpg)

1. Go to Shipping Zone and click "Add Shipping Zone" button. You will see the screen above.

1. Set regions as "Indonesia".

1. Click "Add shipping method" and select "Indo Shipping".

Done! You will now see additional fields for City and District (Kecamatan) when Checkout.

## 3. Changelog

2.2.0 (19 May 2022)

- Refactored the plugin in preparation to be submitted to official repository.

2.1.0 (17 Jan 2022)

- Added support for WooCommerce 5.9.
- Added support for Localhost installation.
- Added volume calculation. If it's higher than weight, it will use volume. The formula is `volume / 6000`.
- Added support for SiCepat, AnterAja, and Ninja couriers.

2.0.0 (24 May 2021)

- Added support for PHP 7.3
- Added support for WooCommerce 5.3
- Splitted the City and District selection into 2 dropdowns
- Added guidance and loading text for better user experience.
- Smoother City and District selection.
- Added condition to disable the selection if Country is not Indonesia.

Planned future updates:

- English language translation
- Support for RajaOngkir free license