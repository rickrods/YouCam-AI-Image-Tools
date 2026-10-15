=== YouCam AI Image Tools ===
Plugin Name: YouCam AI Image Tools
Text Domain: youcam-ai-image-tools
Domain Path: /languages
Contributors: rickrods
Donate link: https://my-portfolio-app-sepia-kappa.vercel.app/
Tags: Image Tools, Image Enhancement, Image Generation, Photo, Editing, Tools woocommerce, Virtual Fitting, Try-On, ai clothes, fashion, perfectcorp, youcam 
Requires at least: 7.0
Tested up to: 7.1.3
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds image generation and enhancement capabilities to Wordpress and turns any WooCommerce apparel store into a virtual dressing room.

== Description ==

- Adds AI Image Generation and AI Photo Enhancement tools powered by YouCam AI API to WordPress.
- Adds virtual try-on capabilities to allow WooCommerce shoppers to upload their photos to try on products like clothing, hats, bags, shoes, and scarves.
- Create product gallery images using YouCam AI API virtual try-on capabilities with model photos.

#### Features:
* Adds AI image generation, headshot generation, avatar generation, and studio-style portrait generation capabilities.
* Add AI photo enhancement tools including Background Change, Image Enhance, Lighting Enhance, Color Correction, Background Blur, Face Swap, Image Extender, Image Colorize, and Watermark Removal.
* Add AI Face & Body enhancement tools including Abs Filter, Aging Simulation, Body Reshape, Breast Augmentation, Face Reshape, Face Lift, and Smile Enhancement.
* Add AI Beauty tools including Bangs Filter, Beard Style, Hair Color, Hair Extention, Hair Style, Hair Volume, Look Try On, Makup Transfer, Nail Transfer, Teeth Whitening, and Wavy Hair tools.
* Setup Product Based API routing to automatically selects the correct API using the WooCommerce product category assigned to the product. Category names or slugs containing `hat`, `cap`, or `headwear` use the Hat API; `scarf`, `scarves`, or `scarfs` use the Scarf API; and `bag`, `purse`, or `handbag` use the Bag API. All other products use the Clothes API. 
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

== Installation ==

This section describes how to install the plugin and get it working.

1. Either `youcam-ai-image-tools.php` to the `/wp-content/plugins/` directory or use the button on the plugins page to install plugin from a file.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enter your API key for YouCam AI API in the plugin Admin Page.

== Frequently Asked Questions ==
* **Where do I get an API key?**
    - [Register for API Here](https://yce.makeupar.com/ai-api)
* **What image format should the product photos be?**
    - We recommend using JPG files for product images.
* **What image format should be used for the Virtual Try On feature?**
    - We recommend using JPG files for product images.
* **What other image formats can be used?**
    - It may be possible to use PNG or WEBP files for some tasks. We discourage PNG files due to their size we have not tested WEBP much at this point but likely has limited support at best.
* **Does the plugin work on mobile?**
    - Yes. The modal and all interactions are fully responsive.

== Screenshots ==
1. Plugin Admin Settings Screen and Sidebar Entries
2. Generate Image Tab when adding images to a Post, Page, or Product
3. Enhancement options when viewing image details from the media library.
4. Virtual Try-On settings for WooCommerce.
5. Create Product Gallery image using Model.

== Changelog ==

= 1.0 =
* Initial public release
* Adds AI image generation, headshot generation, avatar generation, and studio-style portrait generation capabilities.
* Add AI photo enhancement tools including Background Change, Image Enhance, Lighting Enhance, Color Correction, Background Blur, Face Swap, Image Extender, Image Colorize, and Watermark Removal.
* Add AI Face & Body enhancement tools including Abs Filter, Aging Simulation, Body Reshape, Breast Augmentation, Face Reshape, Face Lift, and Smile Enhancement.
* Add AI Beauty tools including Bangs Filter, Beard Style, Hair Color, Hair Extention, Hair Style, Hair Volume, Look Try On, Makup Transfer, Nail Transfer, Teeth Whitening, and Wavy Hair tools.
* Add WooCommerce Virtual Try On features.
* Setup group based access control with limits for guests and users
* Allow editors to create product gallery images using a Model image and the Virtual Try On features.
* Setup usage limits and data retention on accounting info to track usage.
* Created buttons to access image enhancement tools when viewing images in the media gallery.
* Added small, API-based descriptions beneath the inputs across Generate Images, Enhance Images, Enhance Face & Body, and Enhance Beauty. The guidance covers uploads, prompts, styles, presets, and adjustment controls, and also appears in the embedded image-generation and attachment tool modals.
* Allow Image Tools to select input files from media library in addition to using uploaded files.
* Updated styling on the Choose from Media Library tab and buttons
* Standardized image preview sizes to available width, up to 640 px tall, with proportions preserved.


== Arbitrary section ==

The YouCam AI sidebar also contains Image Generation and Image Enhancement pages. 
Administrators, Editors, Authors, and Contributors can use the Image Generation Tools, Image Enhancement Tools, Beauty tools, and Face and Body tools to generate and enhance images.
Headshot styles are loaded from the API. Face Swap maps the first detected target face to the reference photo. 
For try-on, the browser stores a temporary upload token in session storage so the photo can be reused during that browser session; the image itself is uploaded to YouCam and is not stored in browser storage by this plugin. 
The upload token expires on the server after 15 minutes.

The plugin selects the API from the WooCommerce product category assigned to the product. Category names or slugs containing `hat`, `cap`, or `headwear` use the Hat API; `scarf`, `scarves`, or `scarfs` use the Scarf API; and `bag`, `purse`, or `handbag` use the Bag API. All other products use the Clothes API. 

If a product belongs to more than one matching category, the priority is Hat, then Scarf, then Bag. Make sure each product has the appropriate category assigned. For hat, scarf, and bag try-ons, the customer selects the model gender in the modal.

WooCommerce shop owners should ensure that they are labeling their product catagories in such a way that will ensure proper Product based API routing. 

Compliant Product Catagory Names: `hat`, `cap`, `headwear`, `scarf`, `scarves`, `scarfs`, `bag`, `purse`, `handbag` 

All other catagories will be treated as clothing.

When a visitor uploads a photo for virtual try-on, photo generation, or enhancement, the plugin sends the relevant image files to the configured YouCam API service to process the request. Depending on the selected feature, this can include the visitor's photo, a product or model image, and a face-swap reference image. Text prompts and task parameters are also sent when required by the selected feature. YouCam processes these materials under its own terms and privacy practices; site operators should review those terms and disclose this processing to their visitors. The plugin does not control how long YouCam retains submitted files or generated results.

The plugin keeps temporary upload references for up to 15 minutes, try-on task ownership references for up to 24 hours, and poll-throttle markers for up to one day. The browser session stores only the upload token, until the session ends or the token expires. For monthly usage limits and accounting, the plugin stores aggregate request counts and API unit totals keyed by a hash derived from the WordPress user ID or the visitor's IP address, along with the feature and usage month. These hashes are pseudonymous identifiers, not a guarantee of anonymity. In **WooCommerce → Settings → Virtual Try On**, an administrator can choose a usage data retention period in days, weeks, months, or years. Monthly aggregates are deleted after that period has elapsed from the end of their usage month; current-month counters remain so monthly limits continue to work. The default is 30 days. Uninstalling the plugin removes its settings, usage records, plugin transients, and scheduled cleanup event. It leaves images users saved to the WordPress Media Library and data held by the external YouCam service intact.

* TODO
- Support languages via Internationalize Your Plugin
- Update Screenshots
- Prepare to submit
    - Include a 1-3-minute Demo video showing the experience end-to-end
    - Judges are not required to watch beyond three minutes 
    - should explain the YouCam API used in the submission
    - should include footage that shows the Project functioning on the device for which it was built
    - must be uploaded to and made publicly visible on YouTube (highly preferred), Vimeo, or Youku, and a link to the video must be provided on the submission form on the Hackathon Website; and
    - must not include third party trademarks, or copyrighted music or other material unless the Entrant has permission to use such material.