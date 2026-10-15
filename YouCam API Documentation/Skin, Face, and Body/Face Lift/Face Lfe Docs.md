# AI Face Lift

# Overview
AI Face Lift is a generative AI facial enhancement feature that allows precise and natural facial refinement through adjustable parameters. Instead of applying filters, the system intelligently analyzes facial structure, skin quality, and proportions, then reconstructs the image to produce realistic improvements that preserve the individual's identity.

Users can control specific facial areas such as eye bags, cheeks, forehead, overall face shape, and mouth using numeric values from 0 to 100. Each parameter increases the level of enhancement gradually, allowing subtle touch ups or more polished results depending on user preference. All adjustments are designed to remain natural and balanced, avoiding exaggerated or artificial outcomes.

AI Face Lift provides flexible, feature level control for creating a refreshed, confident, and professional appearance suitable for social media, profile photos, creative content, or business use.


![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/01_1_6c0d97eb51.jpg)

![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/01_2_8c428a25dc.jpg)


## Integration Guide
This guide walks you through:

Workflow for AI Face Lift API:

**Endpoint:** `/s2s/v2.0/task/face-lift`

**Authentication Required:** `Authorization: Bearer YOUR_API_KEY`

**Workflow Steps:**

1. **Image Upload Preparation:**
  - Prepare a selfie image for upload.
  - Call the File API `/s2s/v2.0/file` to obtain the upload URL and associated `file_id`.
  - Upload the selfie image using the provided upload URL.

2. **Optional Preprocessing For Multiple Faces:**
    - Preprocess the selfie image if there are more than one face in the image.

3. **Face Lift Effect Setup:**
   - Begin by selecting suitable face lift parameters.

4. **Initiate AI Task and Obtain Task ID:**
   - Send the `file_id` along with the chosen effect configuration via an HTTP POST request to `/s2s/v2.0/task/face-lift`.
   - Await a unique task ID in the response, which identifies this interaction.

5. **Poll Task Status (Continuous Check):**
   - Use the obtained `task_id` to periodically poll the task status using an HTTP GET request (e.g., `GET /s2s/v2.0/task/face-lift${task_id}`).
   - Continuously monitor for:
     - `Task_status = "success"` (process completed).
     - `Task_status = "error"` (resolve or retry if applicable).
   - Update the workflow accordingly once the status transitions to success.

This structured workflow ensures efficient integration with user inputs, automated monitoring of tasks, and seamless retrieval of results.


---

1.  Authentication
    - Include your API key in the request header using **Bearer Token**:
        ```
        Authorization: Bearer YOUR_API_KEY
        ```
    You can find your API Key at https://yce.makeupar.com/api-console/en/api-keys/.


2.  Upload an Image

    You may upload a file directly to the server or provide a valid image URL in the AI task payload.

    * Upload Endpoint

        ```
        POST /s2s/v2.0/file
        ```

    Alternatively, skip this step if you already have a public image URL.

---

3. Select the target face to be enhanced by preprocessing

   * Calling the preprocessing API
    Output detected bounding boxes in pixel coordinate. Use the index of result to create a Face Lift AI task later.
        ```
        POST /s2s/v2.0/task/face-lift/pre-process
        ```

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

4. Create a Face Lift AI Task and Poll for Results

    Once you have an image and a complete effect payload, create a task. The API processes the request asynchronously. You must poll the task status until it reaches `success` or `error`.

   * Create Task Endpoint

        ```
        POST /s2s/v2.0/task/face-lift
        ```

   * Polling Endpoint

        ```
        GET /s2s/v2.0/task/face-lift/{task_id}
        ```

---

## File Specs & Errors

* AI Face Lift Specification

**Supported Selfie View**
Images must be no larger than 1920 x 1920, contain a clearly visible face of sufficient size exceeding 32 x 32 pixels when the long edge is 640, and be captured with a roll angle within plus or minus 75 degrees and a yaw angle within plus or minus 90 degrees to ensure reliable face detection.

![](https://plugins-media.makeupar.com/strapi/assets/thumbnail_face_reshape_01_85c8ffc055.jpg)


---

* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats|
|  ----  | ----  | ----  | ----  |
| AI Face Lift | long side <= 1920 | < 10MB | jpg/jpeg/png |

* Error Codes

| Error Code | Description |
|  ----  | ----  |
| RUNTIME_ERROR | An unexpected error occurred duface lift runtime |
| PHOTO_DETECTION_FAIL | The user photo could not be processed correctly, for example no hand detected |
| OBJECT_DETECTION_FAIL | The object photo could not be processed correctly, for example no product detected |
| PHOTO_CHECK_INVALID | The pose or size of the user photo is invalid |
| INPUT_ERROR | The input file format is incorrect |
| INPUT_MAIN_IMAGE_EMPTY | A user image is required |


* Environment & Dependency

| Sample Code Language / Tool | Recommended Runtime Versions |
|---|---|
| cURL | - bash >= 3.2</br>   - curl >= 7.58 (modern TLS/HTTP support)</br>   - jq >= 1.6 (robust JSON parsing) |
| Node.js (JavaScript) | Node >= 18 (for global fetch) |
| JavaScript | - Chrome / Edge >= 80</br>   - Firefox >= 74</br>   - Safari >= 13.1 |
| PHP | PHP >= 7.4 (for modern TLS/compat), ext-curl (recommended) or allow_url_fopen=On + ext-openssl, ext-json |
| Python | Python >= 3.10 (for f-strings), requests >= 2.20.0 |
| Java | Java 11+ (for HttpClient), Jackson Databind >= 2.12.0 |

---

## JS Camera Kit
{% partial file="/_partials/js-camera-kit.md" /%}

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Face Lift V1.0 | 1 |

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

 - [AI Face Lift](https://docs.perfectcorp.com/_bundle/reference/ai_face_lift.yaml)

## V1.0

 - [POST /s2s/v2.0/task/face-lift/pre-process](https://docs.perfectcorp.com/reference/ai_face_lift/v1.0/paths/~1s2s~1v2.0~1task~1face-lift~1pre-process/post.md): Use the pre-process task when the source image may contain more than one valid target, or when your integration needs to explicitly choose which detected target receives the effect. For single-target
 - [GET /s2s/v2.0/task/face-lift/pre-process/{task_id}](https://docs.perfectcorp.com/reference/ai_face_lift/v1.0/paths/~1s2s~1v2.0~1task~1face-lift~1pre-process~1%7Btask_id%7D/get.md)
 - [POST /s2s/v2.0/task/face-lift](https://docs.perfectcorp.com/reference/ai_face_lift/v1.0/paths/~1s2s~1v2.0~1task~1face-lift/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/face-lift/{task_id}](https://docs.perfectcorp.com/reference/ai_face_lift/v1.0/paths/~1s2s~1v2.0~1task~1face-lift~1%7Btask_id%7D/get.md)