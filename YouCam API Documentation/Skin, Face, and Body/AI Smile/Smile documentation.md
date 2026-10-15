# AI Smile

# overview
Introducing Generative AI Smile API, the easy way to turn that frown upside down. This convenient AI smile generator transforms sad or neutral facial expressions into happy, natural looking smiles in just moments. Powered by advanced generative AI, it helps bring warmth and positivity to any photo with a simple and effortless process. Upload an image, let the AI work its magic, and instantly convert your sad face into a cheerful smiley face that spreads happiness everywhere it's shared.

The AI Smile generator supports two distinct smile styles, giving users more control over the final expression.

1.  **smile_with_teeth_visible**  
    This option creates a bright, joyful smile with naturally visible teeth. It is ideal for upbeat portraits, social media photos, and situations where a warm and expressive look is desired.

2.  **closed_mouth_smile**  
    This option produces a subtle, gentle smile with lips closed. It works well for professional photos, formal profiles, or when a calm and natural expression is preferred.

Users can easily choose the smile type that best matches their photo, mood, or intended use, ensuring realistic and appealing results every time.

Whether you're editing photos, creating fun content, or simply want to add a touch of positivity, Generative AI Smile makes it easy to spread happiness, one smile at a time.

Upload a face. Click once. Smile instantly.

![](https://plugins-media.makeupar.com/smb/blog/post/2024-03-22/5f25b0f7-5d43-421b-ae50-5def0de69f2a.jpg)

![](https://plugins-media.makeupar.com/smb/blog/post/2024-12-16/4f4397e0-9e87-429a-8e39-a9d399b6602b.jpg)

## Integration Guide
This guide walks you through:

Workflow for AI Smile API:

**Endpoint:** `/s2s/v2.0/task/ai-smile`

**Authentication Required:** `Authorization: Bearer YOUR_API_KEY`

**Workflow Steps:**

1. **Image Upload Preparation:**
    - Prepare a selfie image for upload.
    - Call the File API `/s2s/v2.0/file` to obtain the upload URL and associated `file_id`.
    - Upload the selfie image using the provided upload URL.

2. **Initiate AI Task and Obtain Task ID:**
    - Send the `file_id` along with the chosen effect configuration via an HTTP POST request to `/s2s/v2.0/task/ai-smile`.
    - Await a unique task ID in the response, which identifies this interaction.

3. **Poll Task Status (Continuous Check):**
    - Use the obtained `task_id` to periodically poll the task status using an HTTP GET request (e.g., `GET /s2s/v2.0/task/ai-smile/${task_id}`).
    - Continuously monitor for:
        - `Task_status = "success"` (process completed).
        - `Task_status = "error"` (resolve or retry if applicable).
    - Update the workflow accordingly once the status transitions to success.

This structured workflow ensures efficient integration with user inputs, automated monitoring of tasks, and seamless retrieval of results.


---

1. Authentication
- Include your API key in the request header using **Bearer Token**:
    ```
    Authorization: Bearer YOUR_API_KEY
    ```
You can find your API Key at https://yce.makeupar.com/api-console/en/api-keys/.


2. Upload an Image

    You may upload a file directly to the server or provide a valid image URL in the AI task payload.

   * Upload Endpoint

        ```
        POST /s2s/v2.0/file
        ```

    Alternatively, skip this step if you already have a public image URL.

---

3. Create an AI Smile Task and Poll for Results

    Once you have an image and a complete effect setup, create a task. The API processes the request asynchronously. You must poll the task status until it reaches `success` or `error`.

    * Create Task Endpoint

        ```
        POST /s2s/v2.0/task/ai-smile
        ```

    * Polling Endpoint

        ```
        GET /s2s/v2.0/task/ai-smile/{task_id}
        ```

---

## File Specs & Errors

* AI Smile Specification

**Supported Selfie View**
Only single-person images are supported, the image must contain a clearly visible face of sufficient size exceeding 32 x 32 pixels when the long edge is 640, and the capture angles must have a roll within plus or minus 75 degrees and a yaw within plus or minus 90 degrees to avoid face detection failure.

![](https://plugins-media.makeupar.com/strapi/assets/thumbnail_face_reshape_01_85c8ffc055.jpg)


---

* Supported Formats & Dimensions

| AI Feature | Supported Dimensions | Supported File Size | Supported Formats|
|  ----  | ----  | ----  | ----  |
| AI Smile | long side <= 4096 | < 10MB | jpg/jpeg/png/heic |


* Error Codes

| Error Code | Description |
| ---------- | ----------- |
| EXCEED_MAX_FILESIZE  | The input file exceeds the maximum allowed size. |
| INVALID_PARAMETER     | One or more required parameters are missing, empty, or improperly formatted.  |
| ERROR_DOWNLOAD_IMAGE | The source image could not be downloaded. |
| ERROR_NO_FACE        | No face was detected in the provided image. |
| ERROR_INFERENCE       | The inference process failed due to a workflow issue, execution error, encoding error, or missing output image. |
| UNKNOWN_INTERNAL_ERROR | An unexpected internal error occurred. |


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

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Smile V1.0 | 1 |

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

 - [AI Smile](https://docs.perfectcorp.com/_bundle/reference/ai_smile.yaml)

## V1.0

 - [POST /s2s/v2.0/task/ai-smile](https://docs.perfectcorp.com/reference/ai_smile/v1.0/paths/~1s2s~1v2.0~1task~1ai-smile/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/ai-smile/{task_id}](https://docs.perfectcorp.com/reference/ai_smile/v1.0/paths/~1s2s~1v2.0~1task~1ai-smile~1%7Btask_id%7D/get.md)