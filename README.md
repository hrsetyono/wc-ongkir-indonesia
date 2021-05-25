## WooCommerce Indonesia Shipping v2

![Indo Shipping - Checkout Page](https://cdn.pixelstudio.id/indo-shipping/wcis-select-courier.jpg)

Ultimate *Ongkos Kirim* Plugin for major Indonesian Shipping courier.

Add JNE, TIKI, POS, PCP, RPX, and J&T Express to your WooCommerce. Requires PRO License purchase from RajaOngkir.com. We are not affiliated with RajaOngkir in any way.

This plugin is free and provided as is. If you found a bug, please submit it [here](https://github.com/hrsetyono/wc-indo-shipping/issues).

> **Version 2 is now available!**

### Tested working on:

- PHP 7.3 with `CURL` and `ALLOW_URL_FOPEN` enabled.
- WooCommerce 5.3.0

### 1. How to Install in WordPress

1. Download our latest version at https://github.com/hrsetyono/woocommerce-indo-shipping/releases/

1. Go to your WP Admin panel > Plugins > Add New

1. Choose to upload your own zip file.

**Alternative Way using Composer**:

This plugin is available in Composer under this name:

    "pixelstudio/woocommerce-indo-shipping": "~2.0.0"

If you don't know how to use Composer to manage plugins, visit here https://wptips.dev/composer-to-manage-plugins/

### 2. Initial Setup

1. Go to `WooCommerce > Settings > Shipping`. Open "Indo Shipping" tab.

1. Set your API (License) Key and Save. You will find some new fields to set City origin and Services to use. **Note:** If the City dropdown is empty, press Save again.

1. Go to "Shipping Zones" tab and create one with only Indonesia as its region.

1. Click the plus (+) button and add "Indo Shipping".

Done! Now you can see the shipping cost in your Checkout page after filling out all fields.

### 3. Like the Checkout Page Design?

You can have that by simply installing my other plugin: [WooCommerce Edje](https://github.com/hrsetyono/woocommerce-edje)

### 4. Version 2 Changelog

Released 24 May 2021

- Added support for PHP 7.3
- Added support for WooCommerce 5.3
- Splitted the City and District selection into 2 dropdowns
- Added guidance and loading text for better user experience.
- Smoother City and District selection.
- Added condition to disable the selection if Country is not Indonesia.

COMING SOON:
- Add English language translation
- Support for RajaOngkir free license