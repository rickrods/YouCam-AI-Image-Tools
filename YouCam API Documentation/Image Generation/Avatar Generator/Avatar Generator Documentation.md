# AI Avatar Generator

# Overview
For the AI magic avatar tool, this app uses the technology of image-to-image. which means the avatar is generated based on your photo. Once the photos are selected by the users, the technology embedded in the app starts analyzing and learning the user's facial traits.

For more avatar styles, please refer to https://yce.makeupar.com/avatar

Use cases:
![AI Avatar Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/Christmas_Avatar_b861c35edf.jpg "AI Avatar Generator")

![AI Avatar Generator](https://plugins-media.makeupar.com/smb/blog/post/2023-04-06/fc2c3b2e-2b7f-48c9-96c1-cdb780f9dc1d.jpg "AI Avatar Generator")


Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")

---

## File Specs & Errors
* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats |
| ---- | ---- | ----  | ---- |
| AI Avatar Generator | Input: long side <= 4096, Output: long side <= 1024 | < 10MB | jpg/jpeg/png |

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
| AI Avatar Generator V3.0 | 1 unit for 4 images * |

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

 - [AI Avatar Generator](https://docs.perfectcorp.com/_bundle/reference/ai_avatar_generator.yaml)

## V3.0

 - [GET /s2s/v2.0/task/template/ai-avatar](https://docs.perfectcorp.com/reference/ai_avatar_generator/v3.0/paths/~1s2s~1v2.0~1task~1template~1ai-avatar/get.md)
 - [POST /s2s/v2.0/task/ai-avatar](https://docs.perfectcorp.com/reference/ai_avatar_generator/v3.0/paths/~1s2s~1v2.0~1task~1ai-avatar/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/ai-avatar/{task_id}](https://docs.perfectcorp.com/reference/ai_avatar_generator/v3.0/paths/~1s2s~1v2.0~1task~1ai-avatar~1%7Btask_id%7D/get.md)