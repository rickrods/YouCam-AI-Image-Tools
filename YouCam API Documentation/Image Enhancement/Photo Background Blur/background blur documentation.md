# AI Photo Background Blur

# Overview
The bokeh effect is a popular photographic technique used to blur the background of a photo and bring the subject into focus. It adds an artistic touch to a photograph, making it look more professional and eye-catching.

Create professional-looking photos with the AI Photo Background Blur API, which automatically isolates subjects and applies a natural background blur to draw attention where it matters most.


**Sample Usage Scenarios:**

* Portrait Enhancement
Apply a natural bokeh effect to make subjects stand out and improve the visual quality of profile or portrait photos.

    Before:
    ![](https://yce.makeupar.com/assets/images/sod/banner/blur/yce-topbanner-dt-before.jpg)

    After:
    ![](https://yce.makeupar.com/assets/images/sod/banner/blur/yce-topbanner-dt-after.jpg)

* Professional Headshots
Create studio-like background blur effects from standard photos for business profiles and corporate directories.

    Before:
    ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_blur_bg_s3_poster_1_50a314e3f9.jpg)

    After:
    ![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_blur_bg_s3_poster_2_afb0548cb7.jpg)

---

## Integration Guide

**Input Requirements & Processing Criteria:**

- Upload an image containing a clear, prominent foreground subject.
- The image's longest side must not exceed **4,096 px**.
- The source file size must be under **10 MB**.
- At least one clearly visible foreground subject is required.
- Only single-subject analysis is supported. If multiple people are present, the API automatically selects the subject with the largest visible area.


**Workflow:**

1. Call the File API.
2. Retrieve the signed upload URL from the response.
3. Upload the actual image to the returned URL.
4. Create an AI task.
5. Setup a Webhook or Poll the task status until completion.
6. Download the generated result image when processing is successful.

---

**Step 1 — Upload File Metadata Using the File API**

Use `POST /s2s/v2.0/file` to create a file record and receive upload details for the source image.

```bash
curl --request POST \
  --url https://yce-api-01.makeupar.com/s2s/v2.0/file \
  --header 'Authorization: Bearer YOUR_API_KEY' \
  --header 'content-type: application/json' \
  --data '{
    "files": [
      {
        "content_type": "image/jpg",
        "file_name": "full_body_photo_01_3dbd1b6683.jpg",
        "file_size": 547541
      }
    ]
  }'
```

**File API Sample Response:**

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

---

**Step 2 — Retrieve File API Response Details**

The response contains:

| Field | Description |
| --- | --- |
| `file_id` | Identifier used to create the AI task. |
| `requests.url` | Signed URL for uploading the actual image file. |
| `requests.method` | Upload method, usually `PUT`. |
| `requests.headers` | Required headers for the upload request. |

---

**Step 3 — Upload Image to Provided URL**

Use the `requests.url` from the File API response to upload the source image.

```bash
curl --location --request PUT 'https://yce-us.s3-accelerate.amazonaws.com/demo/ttl30/...signature...' \
  --header 'Content-Type: image/jpg' \
  --header 'Content-Length: 547541' \
  --data-binary @'./full_body_photo_01_3dbd1b6683.jpg'
```

---

**Step 4 — Create an AI Task**

Use `POST /s2s/v2.0/task/bg-blur` to create an AI task.

| Parameter | Description | Example |
| --- | --- | --- |
| `src_file_id` | File ID returned from the File API upload flow. Required when using uploaded-file workflow. | `"SaGaqpDgKwFrVBgMpQMA3HY0LeqdT9/13W5TOD8/u/FfjK3xgCQ+hRt9MJXBFaud"` |
| `src_file_url` | Direct URL of the source image. Use this alternative to `src_file_id`. | `"https://example.com/selfie.jpg"` |
| `intensity` | Blue intensity. 0 means no blur, and 100 means the maximum blur. | 50 |

**Example Request:**

```javascript
const resp = await fetch(
  'https://yce-api-01.makeupar.com/s2s/v2.0/task/bg-blur',
  {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: 'Bearer <YOUR_TOKEN_HERE>'
    },
    body: JSON.stringify({
      src_file_url: 'https://example.com/selfie.jpg',
      intensity: 50
    })
  }
);

const data = await resp.json();
console.log(data);
```

**AI Task API Response:**

```json
{
  "status": 200,
  "data": {
    "task_id": "SaGaqpDgKwFrVBgMpQMA3HY0LeqdT9_13W5TOD8_u_GPi6NqQ3dhlmN-6ntFwhzT"
  }
}
```

---

**Step 5 — Setup a Webhook or Poll for Task Result**

See the [webhook integration guide](../develop/webhook.md) for setup and verification details.

For polling, use the returned `task_id` to check task status.

```bash
curl --request GET \
  --url https://yce-api-01.makeupar.com/s2s/v2.0/task/bg-blur/<YOUR_TASK_ID> \
  --header 'Authorization: Bearer YOUR_API_KEY' \
  --header 'content-type: application/json'
```

---

**Step 6 — Retrieve Result Image**

When processing is successful, the response includes a download URL in `data.results.url`.

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

**Invalid API Key Response:**

If the access token is invalid, the API returns a `401` response.

```json
{
  "status": 401,
  "error": "Unauthorized",
  "error_code": "InvalidAccessToken"
}
```

---

## File Specs & Errors

**File Specifications:**

| Specification | Requirement |
| --- | --- |
| Image type | The image must contain one clear and prominent foreground subject or person. |
| Maximum long-side resolution | Long side must not exceed **4096 px**. |
| File size limit | Must be less than **10 MB**. |
| Supported formats | `jpg`, `png`. |

**Error Codes:**

| Error Code | Description |
| --- | --- |
| `exceed_max_filesize` | The source image exceeds the maximum allowed dimensions or file size. The long side must not exceed 4096 px, and the file size must remain below 10 MB. |
| `error_nsfw_content_detected` | Potential NSFW content was detected in the source image or generated result image. |
| `invalid_parameter` | Invalid parameters were provided for source keys, destination keys, actions, mode values, intensity levels, or task configuration. |
| `error_download_image` | The source image could not be downloaded successfully. |
| `error_decode_image` | The source image could not be decoded successfully. |

**Environment & Dependencies:**

| Tool / Language | Recommended Runtime Versions |
| --- | --- |
| cURL | Bash ≥ 3.2; curl ≥ 7.58 with modern TLS/HTTP support; jq ≥ 1.6 for robust JSON parsing. |
| Node.js | Node ≥ 18 for global `fetch` support. |
| JavaScript Browser Support | Chrome / Edge ≥ 80, Firefox ≥ 74, Safari ≥ 13.1. |
| PHP | PHP ≥ 7.4 with modern TLS compatibility; ext-curl recommended or `allow_url_fopen=On` with OpenSSL and JSON support. |
| Python | Python ≥ 3.10 for f-strings; requests ≥ 2.20.0. |
| Java | Java 11+ for HttpClient; Jackson Databind ≥ 2.12.0. |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Photo Background Blur V2.0 | 1 |

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

 - [AI Photo Background Blur](https://docs.perfectcorp.com/_bundle/reference/ai_photo_background_blur.yaml)

## V2.0

 - [POST /s2s/v2.0/task/bg-blur](https://docs.perfectcorp.com/reference/ai_photo_background_blur/v2.0/paths/~1s2s~1v2.0~1task~1bg-blur/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/bg-blur/{task_id}](https://docs.perfectcorp.com/reference/ai_photo_background_blur/v2.0/paths/~1s2s~1v2.0~1task~1bg-blur~1%7Btask_id%7D/get.md)