# YouCam AI Image Tools

#### Plugin Info
```
=== YouCam AI Image Tools ===
Contributors: rickrods
Tags: woocommerce, Virtual Fitting, Try-On, ai clothes, fashion, perfectcorp, youcam
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
```
---
#### Features:
* Adds AI image generation, headshot generation, avatar generation, and studio-style portrait generation capabilities.
* Add AI photo enhancement tools including Background Change, Image Enhance, Lighting Enhance, Color Correction, Background Blur, Face Swap, Image Extender, Image Colorize, and Watermark Removal.
* Add AI Face & Body enhancement tools including Abs Filter, Aging Simulation, Body Reshape, Breast Augmentation, Face Reshape, Face Lift, and Smile Enhancement.
* Add AI Beauty tools including Bangs Filter, Beard Style, Hair Color, Hair Extention, Hair Style, Hair Volume, Look Try On, Makup Transfer, Nail Transfer, Teeth Whitening, and Wavy Hair tools.
* Image Tools that require an input file can use uploads or files from the media library.
* When adding an image or featured image to any Post, Page, or Product the plugin adds an AI Generate tab to allow the creator to input a text description of an image to generate, preview the result, save the result to the media library, and use the result.
* Adds a "Virtual Try-On" button next to the Add to Cart button on product pages.
* Adds a "Model Product" option for product gallery images to allow editors to use the virtual try on tol to create gallery images showing models wearing the products.
* Customers can upload their own full-body photo (JPG, PNG, or WebP).
* Customers can use their uploaded photo to virtualy try on clothing, hats, shoes, scarves, bags, and more.
* Two default model photos (female and male) can be configured so customers can try without uploading their own photos. The model photos can be selected from the media library and the names can be customized.
* WooCommerce product based API routing to ensure requests are routed to the correct API.
* Virtual Try On access control and model settings are under WooCommerce → Settings → Virtual Try On.
* API settings, Usage Limits, and data retention are managed from the YouCam AI WordPress admin menu.
* Group based access control with limits for guests and users
* No build tools or JavaScript frameworks required.
* Responsive modal that works on mobile and desktop.
* AJAX-powered flow — no page reloads required.

---

#### Installation
1. Upload the `youcam-ai-image-tools` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to YouCam AI in the WordPress admin sidebar and enter your API key, Base URL, set usage limits, and configure usage data retention option.
4. Choose default Female and Male model photos from the Media Library and set their display names.
5. Open WooCommerce → Settings → Virtual Try On to enabale guest / signed-in user access and set monthly limits.
6. Visit any WooCommerce product page that has a product image — the "Virtual Try-On" button will appear next to the Add to Cart button.

The YouCam AI sidebar also contains Image Generation and Image Enhancement pages. Administrators, Editors, Authors, and Contributors can use the Image Generator, Headshot Generator, Background Change, Photo Enhance, Smile Enhancement, Photo Lighting, Photo Color Correction, Background Blur, and Face Swap tools. Headshot styles are loaded from the API. Face Swap maps the first detected target face to the reference photo. For try-on, the browser stores a temporary upload token in session storage so the photo can be reused during that browser session; the image itself is uploaded to YouCam and is not stored in browser storage by this plugin. The upload token expires on the server after 15 minutes.



