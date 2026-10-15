# AI Studio Generator

# Overview
Embrace the excellence of studio kike AI Portrait Generator. Transform your selfie into a studio-quality portrait in a flash​.
* Studio-Free Convenience: No need for a photographer or studio visits—create studio-quality artistic photos anytime, anywhere​
* Quick Photo Transformation: Fast processing for instant high-quality artistic photo results, ideal for quick updates
* High-Quality Artistic Output: Delivers professional-standard artistic photos with clear details, perfect lighting, just like you've taken the photos in a studio 

Use cases:
![AI Studio Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_web_AI_studio_S1_img_19b627b6af.jpg "AI Studio Generator")

![AI Studio Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_web_AI_studio_S2_img_01_2e318817e9.jpg "AI Studio Generator")


Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")


---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Studio|Please ensure the input image has one face, a short side of at least 200 pixels, and a long side no greater than 1920 pixels; the engine will select the largest face if multiple are present, and the output resolution will not exceed 960×1280 (W×H)|< 10MB|jpg/jpeg/png|

* Error Codes

|Error Code|Description|
|  ----  | ----  |
|error_below_min_image_size	|Input image resolution is too small|
|error_exceed_max_image_size	|Input image resolution is too large|

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Studio Generator V3.0 | 1 unit for 2 images * |

    > *If the number of images or video duration isn’t evenly divisible, units round up.

---


License: Privacy policy

## Servers

```
https://yce-api-01.makeupar.com
```

## Security

### BearerAuthenticationV2

[object Object]

Type: http
Scheme: bearer

## Download OpenAPI description

 - [AI Studio Generator](https://docs.perfectcorp.com/_bundle/reference/ai_studio_generator.yaml)

## V3.0

 - [GET /s2s/v2.0/task/template/ai-studio](https://docs.perfectcorp.com/reference/ai_studio_generator/v3.0/paths/~1s2s~1v2.0~1task~1template~1ai-studio/get.md)
 - [POST /s2s/v2.0/task/ai-studio](https://docs.perfectcorp.com/reference/ai_studio_generator/v3.0/paths/~1s2s~1v2.0~1task~1ai-studio/post.md): Please refer to the polling guide for checking task status. Requires a source image, template ID, and output count configuration.
 - [GET /s2s/v2.0/task/ai-studio/{task_id}](https://docs.perfectcorp.com/reference/ai_studio_generator/v3.0/paths/~1s2s~1v2.0~1task~1ai-studio~1%7Btask_id%7D/get.md)