# AI Hair Volume Virtual Try-On

# Overview
Enhance Your Look with Fuller, More Voluminous Hair Instantly!​
Add natural volume to fine or thinning hair. Seamlessly fill gaps or add hair with AI. Works for all hair types: straight, curly, thin. Perfect for dating profiles, resumes & more.
Our AI tool helps you achieve perfect hair volume and density in all your photos, whether for personal, professional, or social use. Say goodbye to bad hair days in pictures and hello to fresh, voluminous hair every time.

Use case:
![AI Hair Volume Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/YCE_web_Hair_Volume_Filter_S4_img_01_836436ca00.jpg "AI Hair Volume Generator")

![AI Hair Volume Generator](https://plugins-media.makeupar.com/smb/blog/post/2024-08-26/51eadc51-aaa7-4ebc-ac78-e389be5e16b0.jpg "AI Hair Volume Generator")

Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")

---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Hair Volume Generator|long side <= 1024, face width >= 128, face pose: -10 < pitch < +10, -45 < yaw < +45, -15 < roll < +15, single face only, need to show full face|< 10MB|jpg/jpeg/png|

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
| AI Hair Volume Virtual Try-On V1.0 | 2 |

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

 - [AI Hair Volume Virtual Try-On](https://docs.perfectcorp.com/_bundle/reference/ai_hair_volume.yaml)

## V1.0

 - [GET /s2s/v2.0/task/template/hair-vol](https://docs.perfectcorp.com/reference/ai_hair_volume/v1.0/paths/~1s2s~1v2.0~1task~1template~1hair-vol/get.md)
 - [POST /s2s/v2.0/task/hair-vol](https://docs.perfectcorp.com/reference/ai_hair_volume/v1.0/paths/~1s2s~1v2.0~1task~1hair-vol/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/hair-vol/{task_id}](https://docs.perfectcorp.com/reference/ai_hair_volume/v1.0/paths/~1s2s~1v2.0~1task~1hair-vol~1%7Btask_id%7D/get.md)