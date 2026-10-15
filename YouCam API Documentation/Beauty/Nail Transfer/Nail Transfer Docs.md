# AI Nail Transfer

# Overview
AI Nail Transfer API is a powerful generative AI solution for virtual nail try-on experiences. Powered by YouCam API’s advanced AI models and image transformation technology, users can instantly transfer any desired nail design from a reference photo onto their own hand with remarkable realism and precision.

From gel nails, nail art, and polish finishes to intricate embellishments such as glitter, shell accents, and metallic textures, the AI accurately recreates a wide range of nail styles in a natural, high-definition virtual preview. This enables consumers to explore and visualize designs with confidence before making a purchase.

For beauty brands, salons, and nail artists, AI Nail Transfer API provides a fast and engaging way to showcase nail designs to clients, helping increase customer engagement, improve satisfaction, and drive higher conversion rates through immersive virtual experiences.

![](https://plugins-media.makeupar.com/smb/blog/post/2022-11-24/8821ab6e-9852-4401-89df-dd6005fb81fc.jpg)

## Integration Guide
This guide walks you through:

Workflow for AI Nail Transfer API:

**Endpoint:** `/s2s/v2.0/task/ai-nail`

**Authentication Required:** `Authorization: Bearer YOUR_API_KEY`

**Workflow Steps:**

1. **Image Upload Preparation:**
- The process begins with preparing a hand image via the File API to `/s2s/v2.0/file`.

2. **Reference Photo Upload:**
- Upload a reference photo featuring your desired nail design via the File API to `/s2s/v2.0/file`. For best results, ensure all fingernails are clearly visible.

1. **Initiate AI Task and Obtain Task ID:**
- Send the uploaded image(s) via an HTTP POST request to `/s2s/v2.0/task/ai-nail`.
- Await a unique `task_id` in the response, which identifies this interaction.

1. **Poll Task Status (Continuous Check):**
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


* 1. Upload an Image

You may upload a file directly to the server or provide a valid image URL in the AI task payload.

* Upload Endpoint

```
POST /s2s/v2.0/file
```

Alternatively, skip this step if you already have a public image URL.

---

* 2. Upload a reference photo

You may upload a file directly to the server or provide a valid image URL in the AI task payload. Ensure all fingernails are visible for the most accurate virtual try-on experience.

* Upload Endpoint

```
POST /s2s/v2.0/file
```

---

* 3. Create a AI Nail Transfer AI Task and Poll for Results

Once you have an image and a complete effect payload, create a task. The API processes the request asynchronously. You must poll the task status until it reaches `success` or `error`.

* Create Task Endpoint

```
POST /s2s/v2.0/task/ai-nail
```

* Polling Endpoint

```
GET /s2s/v2.0/task/ai-nail/{task_id}
```

---

## File Specs & Errors

* AI Nail Transfer Specification

    **Supported hand view**

    Supports up to two hands per image. Additional hands will be ignored. For optimal detection, each hand should occupy at least 0.5% of the image area (approximately 102×102 px in FHD, 139×139 px in 2K, and 204×204 px in 4K images).

    ![](https://plugins-media.makeupar.com/strapi/assets/small_webp_bare_nails_014_f6451b0343.png)


    **Supported reference image**

    ![](https://plugins-media.makeupar.com/strapi/assets/small_webp_ref_6_341b11df3d.png)

    ![](https://plugins-media.makeupar.com/strapi/assets/small_webp_ref_2_86a47c1c26.png)

---

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
| ---- | ---- | ---- | ---- |
| AI Nail Transfer | long side <= 4096 | < 10MB | jpg/jpeg/png |

* Error Codes

| Error Code | Description |
| ---- | ---- |
| exceed_max_filesize | Image file is too large |
| invalid_parameter | The input parameters are missing or in wrong format |
| error_download_image | File upload was not complete or the image URL is invalid |
| error_inference | The inference was failed, please check if the input and reference images are supported |
| no_hand_detected | No hand is detected in the source image |


* Environment & Dependency

| Sample Code Language / Tool | Recommended Runtime Versions |
|---|---|
| cURL | - bash >= 3.2</br>- curl >= 7.58 (modern TLS/HTTP support)</br>- jq >= 1.6 (robust JSON parsing) |
| Node.js (JavaScript) | Node >= 18 (for global fetch) |
| JavaScript | - Chrome / Edge >= 80</br>- Firefox >= 74</br>- Safarin b\
| PHP | PHP >= 7.4 (for modern TLS/compat), ext-curl (recommended) orn b\_fopen=On + ext-openssl, ext-json |
| Python | Python >= 3.10 (for f-strings), requests >= 2.20.0 |
| Java | Java 11+ (for HttpClient), Jackson Databind >= 2.12.0 |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Nail Transfer V1.0 | 1 |

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

 - [AI Nail Transfer](https://docs.perfectcorp.com/_bundle/reference/ai_nail_transfer.yaml)

## V1.0

 - [POST /s2s/v2.0/task/ai-nail](https://docs.perfectcorp.com/reference/ai_nail_transfer/v1.0/paths/~1s2s~1v2.0~1task~1ai-nail/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/ai-nail/{task_id}](https://docs.perfectcorp.com/reference/ai_nail_transfer/v1.0/paths/~1s2s~1v2.0~1task~1ai-nail~1%7Btask_id%7D/get.md)