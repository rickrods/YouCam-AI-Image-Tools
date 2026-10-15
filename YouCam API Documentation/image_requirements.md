## What are the recommended photo specification for user and product images?

|Type|Supported Dimensions|Supported File Size|Supported Formats|
|  ---- | ---- | ---- | ---- |
|Target user image|1024×768 recommended, 512×384 minimum, max side 4096 px.</br></br> - Single person only.</br> - The person should occupy at least 80% of the frame for optimal results.</br> - Images should include the upper body only, from the chest upwards. There is no need to show the abdomen, but the shoulders should be visible.</br> - The face must be fully visible, with no obstructions.</br> - The body must be facing forward in a standing position (no sitting or crouching). |< 10MB|jpg/png|
|Reference image of the clothing |1024×768 recommended, 512×384 minimum, max side 4096 px.</br></br> - If Using a Real-Person Clothing Photo as Reference</br>&nbsp;&nbsp;&nbsp;- Must feature only one person.</br>&nbsp;&nbsp;&nbsp;- The visible clothing area must fully cover the intended try-on area.</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Example: For full-body try-on, a half-body clothing image is not acceptable.</br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Example: For lower-body try-on, partial pants are not acceptable.</br>&nbsp;&nbsp;&nbsp;- The clothing must not be heavily obstructed (e.g. covered by long hair or arms).</br>&nbsp;&nbsp;&nbsp;- The face must be fully visible, with no obstructions.</br>&nbsp;&nbsp;&nbsp;- The body must be facing forward in a standing position (no sitting or crouching). </br></br> - If Using a Product Image as Reference</br>&nbsp;&nbsp;&nbsp;- Must be a front-facing product shot of a single garment.</br>&nbsp;&nbsp;&nbsp;- Do not use composite images (e.g. top and bottom in one photo).</br>&nbsp;&nbsp;&nbsp;- For the lower body, only actual worn outfits are supported, not standalone product images.|< 10MB|jpg/png|
- **What are the recommended user photo requirements for Bags Try On API?**
    * Product Image Requirements
        * Minimum resolution: 512 × 512 pixels
        * Only one product per image
        * The product should cover more than 25 per cent of the image height

            ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/040_thumb_c5f4d2af8e.jpg)

    * Worn Image Requirements
        * Minimum resolution: 800 × 800 pixels

            ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/003_thumb_c73b207cae.jpg)

    **Supported Selfie View**

    * Recommended image resolution: at least 512 × 512 pixels.
    * Recommended face coverage: more than 15 per cent of the image height.
    * The image must clearly show a single human subject with the face fully visible and at least a head shot included in the frame, from head to chest. A half-body shot is preferred.

        ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/lashana_lynch_thumb_7a900b811e.jpg)

    **Try-on Styles**

    * There are four predefined styles for generating the virtual try-on output: "style_parisian_chic", "style_urban_chic", "style_mediterranean_chic" and "style_art_deco_style". You can specify this style parameter when creating an AI task or allow the system to randomly select a style by default.

        ![style_parisian_chic](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/fca6a904_b13a_4c90_bc52_d9200a473c70_4d994afa3e.jpg)

    ---

    * Supported Formats & Dimensions

    |AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
    |  ----  | ----  | ----  | ----  |
    |AI Bag Virtual Try-On|Input: long side <= 4096 <br>Output: 1104 x 1472 |< 10MB|jpg/jpeg/png/heic|
- **What are the recommended user photo requirements for Hats Try On API?**  
   * Image Requirements

        | Type   | Minimum Resolution | Notes |
        | ------ | ------------------ | ----- |
        | Selfie | 512 × 512 | Face visible, head-to-chest preferred |
        | Hat  | 512 × 512 (product)<br>800 × 800 (worn) | Clear, unobstructed hat view |
    
        **Supported Hat Image**

        * Product Image Requirements
            * Minimum resolution: 512 × 512 pixels
            * Only one product per image
            * The product should cover more than 25% of the image height

        ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/026_thumb_dca334af3c.jpg)

        * Worn Image Requirements
            * Minimum resolution: 800 × 800 pixels
            * Single Item Requirement: The model must wear exactly one item. Multiple items or accessories are not permitted.
            * Coverage Ratio: The worn item must occupy more than 20% of the total image height. This ensures the item is clearly visible and prominent within the frame.

                ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/010_thumb_51490eebeb.jpg)

        **Supported Selfie View**

        * Recommended image resolution: at least 512 × 512 pixels.
        * Recommended face coverage: more than 15% of the image height.
        * Single Subject Requirement: The image must contain exactly one human subject. No additional people or partial figures are allowed.
        * Face Visibility: The subject's face must be fully visible without obstruction. Hair, accessories, or objects should not cover key facial features.
        * Framing: The image must include at least a head shot, covering the area from the top of the head to the chest. A half-body shot (head to waist) is preferred for optimal analysis.

            ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/lashana_lynch_thumb_7a900b811e.jpg)

        **Try-on Styles**

        * There are five predefined styles for generating the virtual try-on output: "style_sporty_casual" "style_urban_fashion" "style_vacation_casual" "style_warm_cozy" and "style_bohemian". You can specify this style parameter when creating an AI task or allow the system to select a style at random by default.

            ![style_vacation_casual](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/5f42385b_6aef_44cd_b576_2ec10e31305d_824cc2019b.jpg)

- **What are the recommended user photo requirements for Scarfs Try On API?**

    | Type   | Minimum Resolution | Notes |
    | ------ | ------------------ | ----- |
    | Selfie | 512 × 512 | Face visible, head-to-chest preferred |
    | Scarf  | 512 × 512 (product)<br>800 × 800 (worn) | Clear, unobstructed scarf view |

    **Supported Scarf Image**

    * Product Image Requirements
        * Minimum resolution: 512 × 512 pixels
        * Only one product per image
        * The product should cover more than 25 per cent of the image height

    ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/021_thumb_e356d121b3.jpg)

    * Worn Image Requirements
        * Minimum resolution: 800 × 800 pixels

    ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/008_thumb_5dd8b1be93.jpg)

    **Supported Selfie View**

    * Recommended image resolution: at least 512 × 512 pixels.
    * Recommended face coverage: more than 15 per cent of the image height.
    * The image must clearly show a single human subject with the face fully visible and at least a head shot included in the frame, from head to chest. A half-body shot is preferred.

    ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/lashana_lynch_thumb_7a900b811e.jpg)

    **Try-on Styles**

    * There are five predefined styles for generating the virtual try-on output: "style_french_elegance", "style_light_luxury", "style_cottagecore", "style_modern_chic" and "style_bohemian". You can specify this style parameter when creating an AI task or allow the system to select a style at random by default.

    ![style_french_elegance](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/3d88ad75_41ca_4bf8_b81d_f52adf5db263_4a06b3e174.jpg)