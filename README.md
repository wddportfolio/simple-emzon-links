# Simple Emzon Links

Are you ready to generate revenue with minimal efforts from Amazon Affiliate Links?

Simple Emzon Links is a simple WordPress plugin that uses Amazon Product Advertising API and allow you to display desired product from Amazon within your WordPress Post Editor. The generated links are tagged with your Amazon Associate ID.

The plugin is built with the following philosophy:

* Use of sensible and simple **HTML5 markup**
* Provide **realtime** data from Amazon Server - it is up to you to show the plugin to your theme and taste
* **Cache possible** - Default Cache time is 2 hours for rapid load
* Change colors and borders.
* **Gutenberg Block** Support (Amazon Product available in Embed).
* **Shortcode** Support ( [salfwp product_id="B07P9F2ZNP"] ).
* **Easy Setup flow** to configure and go live in minutes.

**COMPATIBLE WITH PA-API 5!**

As of 2019/10/31, Amazon ended PA-API v4 and your site may no longer be able to display Amazon products with old API keys. In that case, regenerate keys on the Amazon Associates member’s area and you are good to go!

**CREDITS**

A plugin by [WDDPortfolio](https://www.wddportfolio.com/ "Websites and Android App Development Company") and [Muhammad Furqan Abid](https://www.mfurqanabid.com/ "WordPress and Laravel developer").

## Installation

To install this plugin:

1. Upload the `simple-emzon-links` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **'Plugins'** menu in **WordPress**.
1. That's it!

Alternatively you can search for the plugin from your WordPress dashboard and install from there.

## Frequently Asked Questions

### Hooks & Filters ###

The plugin has **shipped with cache time controll filter**. You can adjust the cache time for retrieving details from Amazon:

```php
add_filter('salfwp_cache_time', 'salfwp_my_cache_time');

function salfwp_my_cache_time() {
	return HOUR_IN_SECONDS * 2;
}
```

### How To Obtain Access Key and Secret Key ###

Simple Emzon Links require an Access and Secret Keys to perform API requests. 

For detailed instructions, please go to the [Product Advertising API Developer](https://webservices.amazon.com/paapi5/documentation/) section.

#### **Step 1** – Create a Amazon Affiliate Account ####

Before you create an access key, you have to make sure you have singed up with [Amazon Affiliates](https://affiliate-program.amazon.com/).

#### **Step 2** – Create an Access and Secret Key ####

After you sign in your **Amazon Associates account**, in the Amazon Associates page, choose Tools and then choose **Product Advertising API**.

#### Choose **Join**. #### 

After you sign up, copy your **Access Key** and **Secret Key** from **Download credentials** page.

## Changelog

**0.1** - Initial release