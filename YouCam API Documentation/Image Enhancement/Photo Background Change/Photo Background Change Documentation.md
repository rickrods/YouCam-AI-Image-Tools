# AI Photo Background Change

# Overview
The AI Photo Background Change API enhances images by isolating the subject from the original background, enabling a wide range of applications including product-focused use cases in business.

This API enables developers to replace the background using custom prompts or predefined templates.

**Sample Usage**

Before:
![](https://yce.makeupar.com/assets/images/sod/banner/change/yce-topbanner-dt-before.jpg)

After:
![](https://yce.makeupar.com/assets/images/sod/banner/change/yce-topbanner-dt-after.jpg)


Before:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_change_bg_s5_poster_before_0753db3e02.jpg)

After:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/yce_change_bg_s5_poster_after_7fb288fde2.jpg)

---

## Integration Guide

**1. Upload Image**

Request upload URLs and file IDs via:

```
POST /s2s/v2.0/file
```

Upload the image using the returned URL.
Alternatively, provide a publicly accessible image URL hosted on your own storage.


**2. Prepare a background description prompt or select a background template.**

```
GET /s2s/v2.0/task/template/bg-replace
```

Retrieve the list of predefined background templates and select one using its template_id. When using prompt which is the default, the background will be generated based on the provided prompt. When using template, the background will be generated from the predefined template specified by template_id, and the prompt parameter will be ignored.


**3. Execute Analysis Task**

```
POST /s2s/v2.0/task/bg-replace
```

Submit the task using file IDs or image URLs as input, along with the desired background prompt.
The response returns a task_id for tracking and retrieving the result.


**4. Retrieve Task Result**

```
GET /s2s/v2.0/task/bg-replace/{task_id}
```

Use the task ID to track status and obtain results.

[Webhooks](../develop/webhook.md) can be configured to receive asynchronous notifications on task completion with a success or error status. Polling is also supported by repeatedly calling the task endpoint until the status is updated from running to success or error.

Usage is only charged when the task completes successfully.

---

## File Specs & Errors

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
| AI Photo Background Change | The length of the longer side shall not exceed 4096 pixels. | < 10MB | jpg/jpeg/png |

* Error Codes

|Error Code|Description|
|  ----  | ----  |
| exceed_max_filesize |	The input file size exceeds the maximum allowed limit. |
| size_mismatch_on_input_image_and_mask | The input image size must match the input mask image dimensions. |
| invalid_parameter | Invalid parameter value. The request parameter is missing, in an invalid format, or contains an unsupported value.|

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
| AI Photo Background Change V2.0 | 4 |

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

 - [AI Photo Background Change](https://docs.perfectcorp.com/_bundle/reference/ai_photo_background_change.yaml)

## V2.0

 - [GET /s2s/v2.0/task/template/bg-replace](https://docs.perfectcorp.com/reference/ai_photo_background_change/v2.0/paths/~1s2s~1v2.0~1task~1template~1bg-replace/get.md)
 - [POST /s2s/v2.0/task/bg-replace](https://docs.perfectcorp.com/reference/ai_photo_background_change/v2.0/paths/~1s2s~1v2.0~1task~1bg-replace/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/bg-replace/{task_id}](https://docs.perfectcorp.com/reference/ai_photo_background_change/v2.0/paths/~1s2s~1v2.0~1task~1bg-replace~1%7Btask_id%7D/get.md)