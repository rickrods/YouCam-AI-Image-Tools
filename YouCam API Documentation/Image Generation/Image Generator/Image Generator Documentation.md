# AI Image Generator

# Overview
Discover the power of AI with our innovative text-to-image generator! Transform your ideas into stunning visuals instantly, experiment with prompts, explore unique styles like cartoons, oil paintings, or sketches, and let your creativity shine through. Whether you're an artist, designer, or creative soul, our tool offers endless possibilities to bring your vision to life. Add images as references to inspire new artistic directions while letting AI refine them into entirely original masterpieces.

Want more inspirations? Please refer to https://yce.makeupar.com/ai-art-generator.

Use cases:
![AI Image Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/text_to_image_v3_video_02f161f909.jpg "AI Image Generator")

![AI Image Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/text_to_image_v4_poster_092d2fbb9f.jpg "AI Image Generator")

Sample output:
![AI Image Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/text_to_image_topbanner_dt_2_e325681588.jpg "AI Image Generator")

![AI Image Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/text_to_image_topbanner_dt_5_8b4fa13c6a.jpg "AI Image Generator")

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
| ---- | ---- | ---- | ---- |
| V1.0 Text to Image | Output: 1024 pixels on the long side. | Prompt cannot exceed 500 characters | N/A |
| V2.0 Text to Image | Output: The default resolution is 1664 × 928, with supported resolutions of 1664 × 928 (16:9), 1472 × 1104 (4:3), 1328 × 1328 (1:1), 1104 × 1472 (3:4), and 928 × 1664 (9:16). | Prompt cannot exceed 800 characters | N/A |
| V2.0 Image to Image | Input: Both the width and height must fall within the range of 384 to 3072 pixels. <br>Output: Customizable width and height range from 512 to 2,048 pixels, while the default configuration maintains a total pixel count of approximately 1,024 × 1,024 with an aspect ratio based on the input image. | <10MB<br> Prompt cannot exceed 800 characters | JPG, JPEG, PNG, BMP, TIFF, WEBP, and GIF. <br>For animated GIFs, only the first frame is processed. |

* Error Codes

| Error Code | Description |
| ---------- | ----------- |
| exceed_max_filesize | The uploaded file size exceeds the maximum allowed limit. |
| invalid_parameter | One or more parameters are missing or invalid |
| error_download_image | Failed to download the source image. |
| error_decode_image | Failed to decode or parse the source image. |
| error_nsfw_content_detected | Not Safe For Work content was detected in the source image. |
| error_unsupport_ratio | The aspect ratio of the input image is not supported. |
| unknown_internal_error | An unspecified internal error occurred. |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Image Generator V1.0 | 2 |
| AI Image Generator V2.0 | 1 |

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

 - [AI Image Generator](https://docs.perfectcorp.com/_bundle/reference/ai_image_generator.yaml)

## V2.0

 - [POST /s2s/v2.0/task/text-to-image/youcam](https://docs.perfectcorp.com/reference/ai_image_generator/v2.0/paths/~1s2s~1v2.0~1task~1text-to-image~1youcam/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/text-to-image/youcam/{task_id}](https://docs.perfectcorp.com/reference/ai_image_generator/v2.0/paths/~1s2s~1v2.0~1task~1text-to-image~1youcam~1%7Btask_id%7D/get.md)
 - [POST /s2s/v2.0/task/image-to-image/youcam](https://docs.perfectcorp.com/reference/ai_image_generator/v2.0/paths/~1s2s~1v2.0~1task~1image-to-image~1youcam/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/image-to-image/youcam/{task_id}](https://docs.perfectcorp.com/reference/ai_image_generator/v2.0/paths/~1s2s~1v2.0~1task~1image-to-image~1youcam~1%7Btask_id%7D/get.md)
## V1.0

 - [GET /s2s/v2.0/task/template/text-to-image](https://docs.perfectcorp.com/reference/ai_image_generator/v1.0/paths/~1s2s~1v2.0~1task~1template~1text-to-image/get.md)
 - [POST /s2s/v2.0/task/text-to-image](https://docs.perfectcorp.com/reference/ai_image_generator/v1.0/paths/~1s2s~1v2.0~1task~1text-to-image/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/text-to-image/{task_id}](https://docs.perfectcorp.com/reference/ai_image_generator/v1.0/paths/~1s2s~1v2.0~1task~1text-to-image~1%7Btask_id%7D/get.md)