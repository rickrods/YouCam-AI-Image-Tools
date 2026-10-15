# AI Photo Enhance

# Overview
AI Photo Enhance uses advanced AI and deep learning to analyze image details and improve resolution, making low-resolution images clear & fix motion blur.
 * No More Pixelation: Eliminate pixelation for smoother, more defined images.
 * Fix Blurry Photos: Remove blurriness to reveal sharper, crisper details.
 * Enhance Quality: Bring out finer details, making every part of your image stand out.
 * Sharpen Images: Increase sharpness for clearer and more vivid images.
 * Improve Clarity: Boost overall clarity to make your photos look fresh and professional.
 * Face Enhancement: Refine facial features for more lifelike, enhanced portraits in motional images.

Before sample:
![AI Photo Enhance](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_s5_poster_1_0779bbdbdb.jpg "AI Photo Enhance")

After sample:
![AI Photo Enhance](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_s5_poster_2_a2e15250ef.jpg "AI Photo Enhance")


Before sample:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_s2_poster_1_4cd6425807.jpg)

After sample:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_s2_poster_2_7674281362.jpg)

---

## File Specs & Errors
* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats |
| ---- | ---- | ----  | ---- |
| AI Photo Enhance | long side <= 4096 | < 10MB | jpg/jpeg/png |

* Error Codes

|Error Code|Description|
|  ----  | ----  |
| exceed_max_filesize |	Input file size exceeds the maximum limit |
| invalid_parameter |	Invalid parameter value |
| error_download_image	| Download source image error |
| error_decode_image	| Decode source image error |
| error_nsfw_content_detected	| NSFW content detected in source image |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Photo Enhance V1.0 | 2 |

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

 - [AI Photo Enhance](https://docs.perfectcorp.com/_bundle/reference/ai_photo_enhance.yaml)

## V1.0

 - [POST /s2s/v2.0/task/enhance](https://docs.perfectcorp.com/reference/ai_photo_enhance/v1.0/paths/~1s2s~1v2.0~1task~1enhance/post.md): This endpoint initiates the photo enhancement process. You must provide a file ID obtained from the file upload API. The task will be processed asynchronously, and you can check its status using the t
 - [GET /s2s/v2.0/task/enhance/{task_id}](https://docs.perfectcorp.com/reference/ai_photo_enhance/v1.0/paths/~1s2s~1v2.0~1task~1enhance~1%7Btask_id%7D/get.md)