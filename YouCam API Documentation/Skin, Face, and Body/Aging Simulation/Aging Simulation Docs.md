# AI Aging Simulation

# Overview
AI aging generator utilizing generative AI model to generate a series of photos from youth to age from a single selfie image. With the help of AI technology, not only can it measure your current age, but it also lets you see yourself in the future or the past.

![AI Aging Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/f42f1504_79e3_461c_a3f1_a254623d113b_b068736afb.jpg "AI Aging Generator")

The AI aging generator can generate a series of photos based on one single input selfie image. A sample generated photos are shown below as a quick reference of this feature.
![AI Aging Generator](https://bcw-media.s3.ap-northeast-1.amazonaws.com/U_2024_04_17_cr_12112b83e2.png "AI Aging Generator")


## File Specs & Errors
* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats |
| ---- | ---- | ----  | ---- |
| AI Aging | long side <= 4096, single person only. Face pose constraints: yaw within ±30°, roll within ±20°, and pitch within ±20°. | < 10MB | jpg/jpeg |

* Error Codes

|Error Code|Description|
|  ----  | ----  |
| error_below_min_image_size | Source image dimensions must be at least 320 pixels. |
| error_face_position_invalid | Face must be fully visible, forward-facing, and centered in the image. |
| error_face_position_too_small | Detected face is too small for analysis. |
| error_face_position_out_of_boundary | Face extends beyond image boundaries. |
| error_face_not_forward_facing | Face must be directly facing the camera. |
| error_face_angle_upward | Face is angled too far upward—slightly tilt head down. |
| error_face_angle_downward | Face is angled too far downward — slightly tilt head up. |
| error_face_angle_leftward | Face is turned too far left — slightly rotate head right. |
| error_face_angle_rightward | Face is turned too far right — slightly rotate head left. |
| error_face_angle_left_tilt | Face is tilted too far left — gently tilt head right. |
| error_face_angle_right_tilt | Face is tilted too far right — gently tilt head left. |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Aging Simulation V1.0 | 2 |

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

 - [AI Aging Simulation](https://docs.perfectcorp.com/_bundle/reference/ai_aging_simulation.yaml)

## V1.0

 - [POST /s2s/v2.0/task/aging](https://docs.perfectcorp.com/reference/ai_aging_simulation/v1.0/paths/~1s2s~1v2.0~1task~1aging/post.md): This endpoint initiates the aging generation process. You must provide a source file (via URL or File ID). The task will be processed asynchronously, and you can check its status using the task_id ret
 - [GET /s2s/v2.0/task/aging/{task_id}](https://docs.perfectcorp.com/reference/ai_aging_simulation/v1.0/paths/~1s2s~1v2.0~1task~1aging~1%7Btask_id%7D/get.md)