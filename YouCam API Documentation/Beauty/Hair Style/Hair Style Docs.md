# AI Hair Style Virtual Try-On

# Overview
Using the latest AI technology to try a wide variety of hairstyles, catering to both women and men, meeting different gender and style preference.
Discover a world of styles: curly, long, buzz cut, and more. Our AI-powered hair changer lets you experiment effortlessly. Find your ideal hairstyle now!

![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/hair_style_v3_poster_bb1c7aad10.jpg)

---

## Integration Guide

* API Playground
You can use the API Playground to test the AI Hairstyle Generator feature. This allows you to experiment with your ideas and gain a better understanding of the try-on process.

Access the API Playground at:
<https://yce.makeupar.com/api-console/en/api-playground/ai-hair-style-generator/>

---

* API Workflow
This guide walks you through:

Workflow for AI Hairstyle Generator API:

**Endpoint:** `/s2s/v2.1/task/hair-transfer`

**Authentication Required:** `Authorization: Bearer YOUR_API_KEY`

**Workflow Steps:**

1. **Image Upload Preparation:**
   - The process begins with preparing a selfie.

2. **List predefined templates or using your own reference photo**
    **Choose Reference Source**
You have two options for styling references:

| Option | Use Case | Implementation Tip |
|-------|-----------|---------------------|
| **Predefined Templates** (`template_id`) | Quick start (e.g., "Curly Bob", "Side-Swept Bangs") | Call `/s2s/v2.1/task/template/hair-transfer` and pick `template_id`. |
| **Custom Reference Image** (`ref_file_url` / `ref_file_id`) | User uploads own style photo or uses provided image link | Upload via same file API; <BR>Use `ref_file_url` if your reference image is already hosted online. |

3. **Initiate AI Task and Obtain Task ID:**
   - Send the uploaded image along with the style configuration via an HTTP POST request to `/s2s/v2.0/file`.
   - Await a unique task ID in the response, which identifies this interaction.

4. **Poll Task Status (Continuous Check):**
   - Use the obtained `task_id` to periodically poll the task status using an HTTP GET request (e.g., `GET /task/${task_id}`).
   - Continuously monitor for:
     - `Task_status = "success"` (process completed).
     - `Task_status = "error"` (resolve or retry if applicable).
   - Update the workflow accordingly once the status transitions to success.

This structured workflow ensures efficient integration with user inputs, automated monitoring of tasks, and seamless retrieval of results.

---

* Authentication
- Include your API key in the request header using **Bearer Token**:
    ```
    Authorization: Bearer YOUR_API_KEY
    ```
You can find your API Key at https://yce.makeupar.com/api-console/en/api-keys/.

---

* API Usage Guide

This guide explains how to upload images, prepare reference images, and create virtual try-on tasks using the AI Hairstyle Generator API.

***

   * Step 1. Upload a File Using the File API or provide a valid image URL

Use the **File API** (`/s2s/v2.0/file`) to upload a target user image.

Alternatively, skip step 1 to 3 if you already have a public image URL.

**Image Requirements:**

*   Upload a high-resolution selfie photo.
*   Ensure the photo clearly shows the entire body.
*   Avoid backgrounds with multiple people or distracting objects.

**Example Request:**

```bash
curl --request POST \
  --url https://yce-api-01.makeupar.com/s2s/v2.0/file \
  --header 'Authorization: Bearer YOUR_API_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "files": [
      {
        "content_type": "image/jpg",
        "file_name": "selfie_01_3dbd1b6683.jpg",
        "file_size": 547541
      }
    ]
  }'
```

***

   * Step 2. Retrieve File API Response

The response includes:

*   `file_id` for creating an AI task.
*   `requests.url` for uploading the actual image file.

**Sample Response:**

```json
{
  "status": 200,
  "data": {
    "files": [
      {
        "content_type": "image/jpg",
        "file_name": "full_body_photo_01_3dbd1b6683.jpg",
        "file_id": "SaGaqpDgKwFrVBgMpQMA3HY0LeqdT9/13W5TOD8/u/FfjK3xgCQ+hRt9MJXBFaud",
        "requests": [
          {
            "method": "PUT",
            "url": "https://yce-us.s3-accelerate.amazonaws.com/demo/ttl30/...signature...",
            "headers": {
              "Content-Length": "547541",
              "Content-Type": "image/jpg"
            }
          }
        ]
      }
    ]
  }
}
```

***

   * Step 3. Upload Image to Provided URL

Use the `requests.url` from the File API response to upload the image:

```bash
curl --location --request PUT 'https://yce-us.s3-accelerate.amazonaws.com/demo/ttl30/...signature...' \
  --header 'Content-Type: image/jpg' \
  --header 'Content-Length: 547541' \
  --data-binary @'./full_body_photo_01_3dbd1b6683.jpg'
```

***

   * Step 4. Prepare a Reference Image

     * 4.1 Fetch Predefined Image Templates

Use the **Template API** (`/s2s/v2.1/task/template/hair-transfer`) to retrieve a list of predefined reference templates:

```bash
curl --request GET \
  --url 'https://yce-api-01.makeupar.com/s2s/v2.1/task/template/hair-transfer?page_size=20&starting_token=73a3c9e69b89' \
  --header 'Authorization: Bearer YOUR_API_KEY'
```

     * 4.2 Upload a Reference Image

You can:

*   Upload an reference image using the File API (`/s2s/v2.0/file`), or
*   Provide a valid image URL.

**Supported Images:**

*   Another selfie photo as an reference image.

Refer to **[File Specs and Errors](#section/overview/File-Specs-and-Errors)** for detailed specifications.

***

   * Step 5. Create an AI Hairstyle Generator Task

Use the **AI Task API** (`/s2s/v2.1/task/hair-transfer`) to create a virtual try-on task.

**Parameters:**

*   For the user image: `src_file_id` or `src_file_url`.
*   For the reference image: `ref_file_id`, `ref_file_url`, or `template_id`.

**Example Request:**

```bash
curl --request POST \
  --url https://yce-api-01.makeupar.com/s2s/v2.1/task/hair-transfer \
  --header 'Authorization: Bearer YOUR_API_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "src_file_url": "https://plugins-media.makeupar.com/strapi/assets/selfie_03_cccd5d4803.jpeg",
    "ref_file_url": "https://plugins-media.makeupar.com/strapi/assets/style_reference_full_body_01_5a000d999f.png"
  }'
```

**Sample Response:**

```json
{
  "status": 200,
  "data": {
    "task_id": "SaGaqpDgKwFrVBgMpQMA3HY0LeqdT9_13W5TOD8_u_GPi6NqQ3dhlmN-6ntFwhzT"
  }
}
```

***

   * Step 6. Poll for Task Result

Use the task ID to check the status:

```bash
curl --request GET \
  --url https://yce-api-01.makeupar.com/s2s/v2.1/task/hair-transfer/<YOUR_TASK_ID> \
  --header 'Authorization: Bearer YOUR_API_KEY' \
  --header 'content-type: application/json'
```

***

   * Step 7. Retrieve Result

A successful response includes a download URL for the result image:

```json
{
  "status": 200,
  "data": {
    "error": null,
    "results": {
      "url": "https://yce-us.s3-accelerate.amazonaws.com/demo/ttl30/...signature..."
    },
    "task_status": "success"
  }
}
```

Invalid API Key error response:

```json
{
  "status": 401,
  "error": "Unauthorized",
  "error_code": "InvalidAccessToken"
}
```

---

Use cases:
Use case:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/hair_style_v1_video_08513beb46.jpg)

Suggestions for How to Shoot:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/AI_Hair_Extension_recommendation_ba24bd5d92.png)

## File Specs & Errors
* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Hairstyle Generator|long side <= 1024, face width >= 128, face pose: -10 < pitch < +10, -45 < yaw < +45, -15 < roll < +15, single face only, need to show full face|< 10MB|jpg/jpeg|

* Error Codes

|Error Code|Description|
|  ----  | ----  |
|error_no_shoulder	|Shoulders are not visible in the source image
|error_large_face_angle	|The face angle in the uploaded image is too large
|error_insufficient_landmarks	|Cannot detect sufficient face or body landmarks in the source image
|error_hair_too_short	|Input hair is too short
|error_face_pose	|The face pose of source image is unsupported

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

## FAQ
**Q: Can I try on a custom hairstyle?**

**A:** Absolutely, you can try on a custom hairstyle using your own reference photo. The AI Hairstyle Generator supports two methods for specifying the desired hairstyle:

1. **Upload your own reference image**
   You may upload a high-resolution selfie or style photo (e.g., someone wearing the target hairstyle) via the File API (`/s2s/v2.0/file`). After uploading, use the returned `file_id` or public URL as the reference source when creating the AI task.

2. **Provide a valid image URL**
   If your reference image is already hosted online (e.g., on your own server or CDN), you can directly supply its HTTPS URL in the request body under the field `ref_file_url`.

When submitting the task via `/s2s/v2.1/task/hair-transfer`, include either:
- `src_file_id` (your selfie) and `ref_file_id` (your custom reference image),
or
- `src_file_url` and `ref_file_url`.

Ensure both images meet the specified requirements:
- Supported format: JPG/JPEG only
- File size under 10 MB
- Long side ≤ 1024 pixels
- Face width ≥ 128 pixels
- Head pose within allowed range (pitch: −10° to +10°, yaw: −45° to +45°, roll: −15° to +15°)
- Single face visible, full frontal view with clear hair visibility

This flexibility allows you to apply virtually any hairstyle from a photo reference, not just predefined templates.

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Hair Style Virtual Try-On V2.0 | 1 unit for Preset Mode <br> 2 units for Custom Mode |
| AI Hair Style Virtual Try-On V2.1 | 2 units for Preset Mode <br> 2 units for Custom Mode |

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

 - [AI Hair Style Virtual Try-On](https://docs.perfectcorp.com/_bundle/reference/ai_hairstyle.yaml)

## V2.1

 - [GET /s2s/v2.1/task/template/hair-transfer](https://docs.perfectcorp.com/reference/ai_hairstyle/v2.1/paths/~1s2s~1v2.1~1task~1template~1hair-transfer/get.md)
 - [POST /s2s/v2.1/task/hair-transfer](https://docs.perfectcorp.com/reference/ai_hairstyle/v2.1/paths/~1s2s~1v2.1~1task~1hair-transfer/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.1/task/hair-transfer/{task_id}](https://docs.perfectcorp.com/reference/ai_hairstyle/v2.1/paths/~1s2s~1v2.1~1task~1hair-transfer~1%7Btask_id%7D/get.md)
## V2.0

 - [POST /s2s/v2.0/task/hair-transfer](https://docs.perfectcorp.com/reference/ai_hairstyle/v2.0/paths/~1s2s~1v2.0~1task~1hair-transfer/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/hair-transfer/{task_id}](https://docs.perfectcorp.com/reference/ai_hairstyle/v2.0/paths/~1s2s~1v2.0~1task~1hair-transfer~1%7Btask_id%7D/get.md)
## V1.0

 - [GET /s2s/v2.0/task/template/hair-style](https://docs.perfectcorp.com/reference/ai_hairstyle/v1.0/paths/~1s2s~1v2.0~1task~1template~1hair-style/get.md)
 - [POST /s2s/v2.0/task/hair-style](https://docs.perfectcorp.com/reference/ai_hairstyle/v1.0/paths/~1s2s~1v2.0~1task~1hair-style/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/hair-style/{task_id}](https://docs.perfectcorp.com/reference/ai_hairstyle/v1.0/paths/~1s2s~1v2.0~1task~1hair-style~1%7Btask_id%7D/get.md)