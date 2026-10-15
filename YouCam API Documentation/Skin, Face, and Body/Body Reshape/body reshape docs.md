# AI Body Reshape

# Overview
AI Body Reshape API for Body Reshape & Slimming
Feel confident in photos with an AI body editor! Slim, reshape, and enhance your body for natural, stunning proportions. Effortlessly reshape any area — waist, arms, thighs, chest, and more. Get natural, stunning results in just a tap!


## Integration Guide
This guide walks you through:

Workflow for AI Body Reshape API:

**Endpoint:** `/s2s/v2.0/task/body-reshape`

**Authentication Required:** `Authorization: Bearer YOUR_API_KEY`

**Workflow Steps:**

1. **Image Upload Preparation:**
    - Prepare a selfie image for upload.
    - Call the File API `/s2s/v2.0/file` to obtain the upload URL and associated `file_id`.
    - Upload the selfie image using the provided upload URL.

2. **Optional Preprocessing For Group Photos:**
    - Preprocess the selfie image if there are more than one person in the image.

3. **Body Reshape Effect Setup:**
    - Begin by selecting suitable body reshape parameters.

4. **Initiate AI Task and Obtain Task ID:**
    - Send the `file_id` along with the chosen effect configuration via an HTTP POST request to `/s2s/v2.0/task/body-reshape`.
    - Await a unique task ID in the response, which identifies this interaction.

5. **Poll Task Status (Continuous Check):**
    - Use the obtained `task_id` to periodically poll the task status using an HTTP GET request (e.g., `GET /s2s/v2.0/task/body-reshape/${task_id}`).
    - Continuously monitor for:
    - `Task_status = "success"` (process completed).
    - `Task_status = "error"` (resolve or retry if applicable).
    - Update the workflow accordingly once the status transitions to success.

This structured workflow ensures efficient integration with user inputs, automated monitoring of tasks, and seamless retrieval of results.

---

* API Playground

Interactively explore and test the API using our official playground:

**API Playground:**
[http://yce.makeupar.com/api-console/en/api-playground/ai-body-reshape/](http://yce.makeupar.com/api-console/en/api-playground/ai-body-reshape/)

---

* Authentication
- Include your API key in the request header using **Bearer Token**:
```
Authorization: Bearer YOUR_API_KEY
```
You can find your API Key at https://yce.makeupar.com/api-console/en/api-keys/.


* 1. Upload an Image

You may upload a file directly to the server or provide a valid image URL in the AI task payload.

* Upload Endpoint

```
POST /s2s/v2.0/file
```

Alternatively, skip this step if you already have a public image URL.

---

* 2. Prepare an effect template

* Preprocessing
Output detected bounding boxes in pixel coordinate. Use the index of result to create a Body Reshape AI task later.
```
{
"timed": number,
"result": [
{
 "left": number,
 "top": number,
 "width": number,
 "height": number
}
]
}
```

* Effect Template JSON Schemas
```
{
"version": "1.0",
"index": 0,
"features": {
"arm": 0, // -100~100
"breast_left": 0, // -100~100
"breast_right": 0, // -100~100
"hip": 0, // -100~100
"hip_lift": 0, // -100~100
"leg": 0, // -100~100
"neck_left": 0, // 0~100
"neck_right": 0, // 0~100
"shoulder_left": 0, // -100~100
"shoulder_right": 0, // -100~100
"squared_shoulder_left": 0, // -100~100
"squared_shoulder_right": 0, // -100~100
"slim": 0, // -100~100
"taller": 0, // 0~100
"waist": 0, // -100~100
"belly": 0 // -100~100
}
}
```
index: index of detected body from preprocessing. optional, default 0.
features: required at least 1, non-zero body reshape parameter, cannot be all zero.


* Example Payload (ready to send)
```
{
 "src_file_url": "https://plugins-media.makeupar.com/strapi/assets/body_reshape_02_7777218379.jpg",
 "version": "1.0",
 "index": 0,
 "features": {
"arm": 0,
"waist": -20,
"taller": 80,
"squared_shoulder_left": 10,
"squared_shoulder_right": 10,
"neck_left": 0,
"neck_right": 0,
"hip": 10,
"breast_left": 30,
"breast_right": 30,
"slim": -70,
"shoulder_left": 30,
"shoulder_right": 30,
"leg": -30,
"hip_lift": 0,
"belly": -50
 }
}
```


* 3. Create a Body Reshape AI Task and Poll for Results

Once you have an image and a complete effect payload, create a task. The API processes the request asynchronously. You must poll the task status until it reaches `success` or `error`.

* Create Task Endpoint

```
POST /s2s/v2.0/task/body-reshape
```

* Polling Endpoint

```
GET /s2s/v2.0/task/body-reshape/{task_id}
```

---

## File Specs & Errors

* AI Body Reshape Specification

**Supported Selfie View**
Full body shot with clear facial expression and body posture visible.

![](https://plugins-media.makeupar.com/strapi/assets/thumbnail_body_reshape_02_7777218379.jpg)

**Visibility & Pose Requirements**

| **Region** | **What must be true in the source image** |
|------------|---------------------------------------------------|
| **Neck** | The neck must be visible in the source image. |
| **Arm** | Both arms – upper arm, forearm and hand – have to be fully shown. |
| **Leg** | The whole leg must appear in the picture. |
| **Hip** | Hips need to be in view. |
| **Hip‑Lift** | Hips need to be in view. |
| **Shoulder** | • Shoulders are required to be seen.<br>• Raising a hand is not allowed.<br>• A 90° side pose is not allowed. |
| **Belly** | The belly is required to be visible. |
| **Waist** | Waist must be visible. |
| **Breast** | • Breast area must appear in the picture.<br>• The shot should include the hips (i.e., not a “half‑body without hips”).<br>• The middle of the breast must be shown. |
| **Slim** | Shoulders need to be in the frame. |
| **Taller** | • Hips must appear.<br>• A half‑body view should include the legs (i.e., legs are visible). |



**Body Reshape Customization Parameters Guide**

| Category | Parameter | Function | Min Value (-100 / 0) | Max Value (100) |
| -------- | --------- | -------- | -------------------- | --------------- |
| Arms| Intensity | Adjusts the thickness of the arms | Thin | Thick |
| Belly| Intensity | Adjusts abdominal projection | Flat | Protruding |
| Chest| Intensity (Left / Right) | Adjusts chest volume on each side | Flat | Full |
| Hip Lift | Intensity | Adjusts the curvature and lift of the hips (adds a butt‑lifting effect) | Sculpted (lifted and tightened) | Rounded (lifted with fuller volume) |
| Hip Size | Intensity | Adjusts the overall width of the hips | Narrow | Wide |
| Legs| Intensity | Adjusts the thickness of the legs | Thin | Thick|
| Neck| Intensity (Left / Right) | Adjusts neck contour and slimming per side| Original (0)| Tucked in |
| Shoulder Width | Intensity (Left / Right) | Adjusts the span of each shoulder | Narrow | Broad |
| Shoulder Shape | Intensity (Left / Right) | Adjusts the angle and slope of each shoulder | Sloped | Squared |
| Slim| Intensity | Adjusts overall body curvature | Slim | Curvy |
| Tall| Intensity | Adjusts the overall character height | Original (0) | Taller |
| Waist| Intensity | Adjusts the width of the waistline | Narrow | Wide |

**Note:** *“Left” and “Right” refer to the character's perspective, not the viewer's side of the screen.*


---

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
| ---- | ---- | ---- | ---- |
|AI Body Reshape|long side <= 2048, short side >= 320|< 10MB|jpg/jpeg/png|

* Error Codes

| Error Code | Description |
| ---- | ---- |
| RUNTIME_ERROR | An unexpected error occurred dubody reshape runtime |
| PHOTO_DETECTION_FAIL | The user photo could not be processed correctly, for example no hand detected |
| PHOTO_CHECK_INVALID | The pose or size of the user photo is invalid |
| INPUT_ERROR | The input file format is incorrect |
| INPUT_MAIN_IMAGE_EMPTY | A user image is required |


* Environment & Dependency

| Sample Code Language / Tool | Recommended Runtime Versions |
|---|---|
| cURL | - bash >= 3.2</br>- curl >= 7.58 (modern TLS/HTTP support)</br>- jq >= 1.6 (robust JSON parsing) |
| Node.js (JavaScript) | Node >= 18 (for global fetch) |
| JavaScript | - Chrome / Edge >= 80</br>- Firefox >= 74</br>- Safari >= 13.1 |
| PHP | PHP >= 7.4 (for modern TLS/compat), ext-curl (recommended) or allow_url_fopen=On + ext-openssl, ext-json |
| Python | Python >= 3.10 (for f-strings), requests >= 2.20.0 |
| Java | Java 11+ (for HttpClient), Jackson Databind >= 2.12.0 |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Body Reshape V1.0 | 1 |

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

 - [AI Body Reshape](https://docs.perfectcorp.com/_bundle/reference/ai_body_reshape.yaml)

## V1.0

 - [POST /s2s/v2.0/task/body-reshape/pre-process](https://docs.perfectcorp.com/reference/ai_body_reshape/v1.0/paths/~1s2s~1v2.0~1task~1body-reshape~1pre-process/post.md): Use the pre-process task when the source image may contain more than one valid target, or when your integration needs to explicitly choose which detected target receives the effect. For single-target
 - [GET /s2s/v2.0/task/body-reshape/pre-process/{task_id}](https://docs.perfectcorp.com/reference/ai_body_reshape/v1.0/paths/~1s2s~1v2.0~1task~1body-reshape~1pre-process~1%7Btask_id%7D/get.md)
 - [POST /s2s/v2.0/task/body-reshape](https://docs.perfectcorp.com/reference/ai_body_reshape/v1.0/paths/~1s2s~1v2.0~1task~1body-reshape/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/body-reshape/{task_id}](https://docs.perfectcorp.com/reference/ai_body_reshape/v1.0/paths/~1s2s~1v2.0~1task~1body-reshape~1%7Btask_id%7D/get.md)