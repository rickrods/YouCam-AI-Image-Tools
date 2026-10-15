# AI Color Correction

# Overview
Perfect AI Color Correction let you automatically adjust saturation, temperature, and hue of photos with ease. Adjust white balance to correct color temperature, enhance saturation and make it vibrant, correct exposure level to balance brightness, remove color casts or tints, improve skin tones for a nature-looking portrait, enhance shadow and highlight details, remove noise and improve clarity, or even apply creative color grading effects all in one touch. With AI Color Correction, you can instantly generate 4 different color graded versions of your photos, each with unique color tones ranging from warm to cool within seconds.

Sample
Before:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/color_correction_topbanner_before_dt_100_45429e8625.jpg)

After:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/color_correction_topbanner_after_dt_100_ef9f9c48ed.jpg)



Before:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/color_correction_s1_image_before_dt_fa73b5d41a.jpg)

After
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/color_correction_s1_image_after_dt_a3e88a101b.jpg)


---

## File Specs & Errors
* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats |
| ---- | ---- | ----  | ---- |
| AI Color Correction | long side <= 4096 | < 10MB | jpg/jpeg/png |

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
| AI Color Correction V1.0 | 2 |

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

 - [AI Color Correction](https://docs.perfectcorp.com/_bundle/reference/ai_color_correction.yaml)

## V1.0

 - [POST /s2s/v2.0/task/colorize/color-correct](https://docs.perfectcorp.com/reference/ai_color_correction/v1.0/paths/~1s2s~1v2.0~1task~1colorize~1color-correct/post.md): Please refer to the polling guide for checking task status.
 - [GET /s2s/v2.0/task/colorize/color-correct/{task_id}](https://docs.perfectcorp.com/reference/ai_color_correction/v1.0/paths/~1s2s~1v2.0~1task~1colorize~1color-correct~1%7Btask_id%7D/get.md)