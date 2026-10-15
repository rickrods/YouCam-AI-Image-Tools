# AI Hair Extension Virtual Try-On

# Overview
Discover Your Perfect Hair Extension Match with AI​
Experiment with a variety of lengths—from long to extra-long—styles, colors, and bangs, all from the comfort of your device. No more guessing games—see exactly how each hair extension style looks on you with the advanced Generative AI. Make informed styling decisions before committing to a new look.​
With the advanced Hair Extension Try-On, which naturally blends with your current hair length, it’s the perfect time to experiment with super-long styles.

Use case:
![AI Hair Extension](https://bcw-media.s3.ap-northeast-1.amazonaws.com/YCE_web_Hair_Extension_Filter_S2_img_07_098b6e08c4.jpg "AI Hair Extension")

![AI Hair Extension](https://bcw-media.s3.ap-northeast-1.amazonaws.com/YCE_web_Hair_Extension_Filter_S1_img_01_eab88fe3e2.jpg "AI Hair Extension")

Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")

---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Hair Extension|long side <= 1024, face width >= 128, face pose: -10 < pitch < +10, -45 < yaw < +45, -15 < roll < +15, single face only, need to show full face|< 10MB|jpg/jpeg|

* Error Codes

|Error Code|Description|
|  ----  | ----  |
|error_no_shoulder	|Shoulders are not visible in the source image
|error_large_face_angle	|The face angle in the uploaded image is too large
|error_insufficient_landmarks	|Cannot detect sufficient face or body landmarks in the source image
|error_hair_too_short	|Input hair is too short
|error_face_pose	|The face pose of source image is unsupported
|error_bald_image	|Input hairstyle is bald

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Hair Extension Virtual Try-On V1.0 | 1 |

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

 - [AI Hair Extension Virtual Try-On](https://docs.perfectcorp.com/_bundle/reference/ai_hair_extension.yaml)

## V1.0

 - [GET /s2s/v2.0/task/template/hair-ext](https://docs.perfectcorp.com/reference/ai_hair_extension/v1.0/paths/~1s2s~1v2.0~1task~1template~1hair-ext/get.md)
 - [POST /s2s/v2.0/task/hair-ext](https://docs.perfectcorp.com/reference/ai_hair_extension/v1.0/paths/~1s2s~1v2.0~1task~1hair-ext/post.md): This endpoint initiates the hair extension generation process using a template and source image. The task will be processed asynchronously, and you can check its status using the task_id returned in t
 - [GET /s2s/v2.0/task/hair-ext/{task_id}](https://docs.perfectcorp.com/reference/ai_hair_extension/v1.0/paths/~1s2s~1v2.0~1task~1hair-ext~1%7Btask_id%7D/get.md)