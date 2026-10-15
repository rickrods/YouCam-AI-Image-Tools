# AI Bangs Filter Virtual Try-On

# Overview
Try on Your Perfect Hair Bangs with AI
Realistic Looks: Experiment with realistic bangs and discover the style that best complements your face.​
Versatile Styling Options: Explore a wide range of bangs styles to suit every personality and occasion.​
Effortless Experience: Enjoy a user-friendly interface that makes trying new bangs easy and fun​.

Want to see more Hair Bang styles? Please refer to https://yce.makeupar.com/bangs-filter.

Use case:

![AI Hair Bang Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/hair_style_v1_video_1200x674px_1_259f619dfd.png "AI Hair Bang Generator")

![AI Hair Bang Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/hair_style_v1_video_1200x674px_2_7146754733.png "AI Hair Bang Generator")


Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")

---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Hair Bang Generator|long side <= 1024, face width >= 128, face pose: -10 < pitch < +10, -45 < yaw < +45, -15 < roll < +15, single face only, need to show full face|< 10MB|jpg/jpeg/png|

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
| AI Bangs Filter Virtual Try-On V1.0 | 1 |

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

 - [AI Bangs Filter Virtual Try-On](https://docs.perfectcorp.com/_bundle/reference/ai_bangs.yaml)

## V1.0

 - [GET /s2s/v2.0/task/template/hair-bang](https://docs.perfectcorp.com/reference/ai_bangs/v1.0/paths/~1s2s~1v2.0~1task~1template~1hair-bang/get.md)
 - [POST /s2s/v2.0/task/hair-bang](https://docs.perfectcorp.com/reference/ai_bangs/v1.0/paths/~1s2s~1v2.0~1task~1hair-bang/post.md): This endpoint initiates the hair bang generation process using a template and source image. The task will be processed asynchronously, and you can check its status using the task_id returned in this r
 - [GET /s2s/v2.0/task/hair-bang/{task_id}](https://docs.perfectcorp.com/reference/ai_bangs/v1.0/paths/~1s2s~1v2.0~1task~1hair-bang~1%7Btask_id%7D/get.md)