# AI Headshot Generator

# Overview
Transform your photos into stunning professional headshots with our AI Headshot Generator. Elevate your headshots quickly and effectively using our powerful AI tools designed to deliver professional-quality results.
* Variety of Styles: From polished LinkedIn headshots and professional business headshots to creative model headshots, our AI headshot generator helps you select the perfect look to suit your needs.
* Professional Results: Leveraging AI to ensure your headshots look natural and flattering, making a strong impression on potential employers and clients.
* Convenience: Generate multiple AI headshots anytime, anywhere, without the need for a photographer. Perfect for busy professionals.

For more AI Headshot styles, please refer to https://yce.makeupar.com/ai-headshot-generator.

Use cases:
![AI Headshot Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_headshot_s2_img_03_4b55742358.jpg "AI Headshot Generator")

![AI Headshot Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_headshot_s1_img_1_d03183d7e0.jpg "AI Headshot Generator")


Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")


---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
| AI Headshot Generator | Ensure the input image contains a single person with both shoulder points and a full face visible from OpenPose, and that its short side is ≤ 1024 pixels — otherwise, the engine will automatically resize it to 1024. Output: long side <= 1024 | < 10MB | jpg/jpeg/png |

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
| AI Headshot Generator V1.0 | 1 unit for 2 images * |

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

 - [AI Headshot Generator](https://docs.perfectcorp.com/_bundle/reference/ai_headshot_generator.yaml)

## V1.0

 - [GET /s2s/v2.0/task/template/headshot](https://docs.perfectcorp.com/reference/ai_headshot_generator/v1.0/paths/~1s2s~1v2.0~1task~1template~1headshot/get.md)
 - [POST /s2s/v2.0/task/headshot](https://docs.perfectcorp.com/reference/ai_headshot_generator/v1.0/paths/~1s2s~1v2.0~1task~1headshot/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/headshot/{task_id}](https://docs.perfectcorp.com/reference/ai_headshot_generator/v1.0/paths/~1s2s~1v2.0~1task~1headshot~1%7Btask_id%7D/get.md)