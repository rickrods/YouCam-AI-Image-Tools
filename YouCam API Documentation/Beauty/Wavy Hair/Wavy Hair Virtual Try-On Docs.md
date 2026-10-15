# AI Wavy Hair Virtual Try-On

# Overview
Whether you're dreaming of bouncy ringlets, loose waves, or a bold curly statement, the YouCam API’s curly hair filter lets you experiment with a fresh, fabulous hairstyle in seconds—all from the comfort of home.

Whether you're trying a soft wave or a bold afro, YouCam delivers a level of precision and realism that sets it apart. It's the ideal tool for exploring new hairstyles with confidence before your next salon visit.


![](https://plugins-media.makeupar.com/smb/blog/post/2025-04-01/webp_03963789-fb1e-4ad8-be4d-48932e247376.jpg)

![](https://plugins-media.makeupar.com/smb/blog/post/2025-03-20/b25b228a-259b-4efb-a7c1-31d200b62e8c.jpg)

Suggestions for How to Shoot:
![Suggestions for How to Shoot](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png "Suggestions for How to Shoot")

---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Wavy Hair|long side <= 1024, face width >= 128, face pose: -10 < pitch < +10, -45 < yaw < +45, -15 < roll < +15, single face only, need to show full face|< 10MB|jpg/jpeg/png|

* Error Codes

|Error Code|Description|
|  ----  | ----  |
|error_no_shoulder	|Shoulders are not visible in the source image
|error_large_face_angle	|The face angle in the uploaded image is too large
|error_insufficient_landmarks	|Cannot detect sufficient face or body landmarks in the source image
|error_hair_too_short	|Input hair is too short
|error_face_pose	|The face pose of source image is unsupported

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Wavy Hair Virtual Try-On V1.0 | 1 |

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

 - [AI Wavy Hair Virtual Try-On](https://docs.perfectcorp.com/_bundle/reference/ai_wavy_hair.yaml)

## V1.0

 - [GET /s2s/v2.0/task/template/hair-curl](https://docs.perfectcorp.com/reference/ai_wavy_hair/v1.0/paths/~1s2s~1v2.0~1task~1template~1hair-curl/get.md)
 - [POST /s2s/v2.0/task/hair-curl](https://docs.perfectcorp.com/reference/ai_wavy_hair/v1.0/paths/~1s2s~1v2.0~1task~1hair-curl/post.md): Please refer to the polling guide for checking task status.
 - [GET /s2s/v2.0/task/hair-curl/{task_id}](https://docs.perfectcorp.com/reference/ai_wavy_hair/v1.0/paths/~1s2s~1v2.0~1task~1hair-curl~1%7Btask_id%7D/get.md)