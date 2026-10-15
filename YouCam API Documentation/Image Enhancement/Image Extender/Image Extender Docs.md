# AI Image Extender

# Overview
Experience vibrant AI Outpainting with our cutting-edge AI Image Extender. Seamlessly expand images in any ratio, bringing out your creativity with our advanced AI technology. Preserve the highest quality while expanding your photos without compromising on style or aesthetics. Instantly transform your photos with one-click automatic background enlargement thanks to our user-friendly AI tool. Thanks to our advanced context-aware technology, we ensure a seamless and captivating experience for every viewer.

![AI Image Extender](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_web_outpainting_S1_img_03_cf19a018d9.jpg "AI Image Extender")

Whether you're aiming for Instagram glory or framing a digital masterpiece, select from a variety of sizes and ratios for that perfect fit. As you tweak and transform, our AI seamlessly weaves its magic, ensuring the expanded areas blend flawlessly with your original photo.

![AI Image Extender](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_web_outpainting_S1_img_01_1876fb85a5.jpg "AI Image Extender")


## File Specs & Errors
* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats |
| ---- | ---- | ----  | ---- |
| AI Image Extender | long side <= 4096 | < 10MB | jpg/jpeg |

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
| AI Image Extender V2.0 | 2 |

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

 - [AI Image Extender](https://docs.perfectcorp.com/_bundle/reference/ai_image_extender.yaml)

## V2.0

 - [POST /s2s/v2.0/task/out-paint](https://docs.perfectcorp.com/reference/ai_image_extender/v2.0/paths/~1s2s~1v2.0~1task~1out-paint/post.md): Please refer to the polling guide for checking task status. The API takes 3 key parameters: input image, pivot point, and output image.
 - [GET /s2s/v2.0/task/out-paint/{task_id}](https://docs.perfectcorp.com/reference/ai_image_extender/v2.0/paths/~1s2s~1v2.0~1task~1out-paint~1%7Btask_id%7D/get.md)