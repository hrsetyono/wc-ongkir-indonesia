## WooCommerce Indonesia Shipping

**REQUIREMENT**: PHP 5.6 and WooCommerce 2.6+

Add JNE, TIKI, or POS Shipping (Ongkos Kirim) to your WooCommerce. Requires PRO License purchase from RajaOngkir.com.

This plugin is free and provided as is. If you found a bug, please submit it [here](https://github.com/hrsetyono/wc-indo-shipping/issues).

**KNOWN BUG**: This plugin only works if the only shipping option is Indonesia. We will patch it next version.

### How to Use

1. Go to WooCommerce > Settings > Shipping. Open "Indo Shipping" tab.

1. Set your API (License) Key and Save. You will find some new fields to set City origin and Services to use. **Note:** If the City dropdown is empty, press Save again.

1. Go to "Shipping Zones" tab and create one with only Indonesia as its region.

1. Click the plus (+) button and add "Indo Shipping".

Done! Now you can see the shipping cost in your Checkout page after filling out all fields.

### Screenshot

![Setting Page](http://cdn.setyono.net/indo-shipping/setting.jpg)

**SETTING PAGE** - After API connected, you can select the City origin (based on your Shop Location) and select which services you want.

-----

![Shipping Zone](http://cdn.setyono.net/indo-shipping/zone.jpg)

**SHIPPING ZONE** - Create 'Indonesia' region and set 'Indo Shipping' method.

-----

![Checkout Page](http://cdn.setyono.net/indo-shipping/checkout.jpg)

**CHECKOUT PAGE** - New City and District (*Kecamatan*) dropdown will be created. After filling all fields, the Shipping option will be shown.

### FAQ

1. **Where to get Raja Ongkir API?** Register at [rajaongkir.com](http://rajaongkir.com/) and purchase PRO version. Sorry, we don't support any other version.

1. **Why does my API Key always wrong?** Make sure there's no empty space before or after the fields.

1. **The Province / City info doesn't show up during Checkout!** There are two common problems: (1) Your host doesn't support a feature called "CURL", ask your customer support about this, and (2) Your theme has heavily modified Checkout page; go to Appearance > Theme and download a theme called "Storefront" and see whether you still got the issue.
