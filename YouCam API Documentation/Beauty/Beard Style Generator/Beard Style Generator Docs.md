# AI Beard Style Generator

# Overview
AI Simulation for Men's Beard Styles

The AI algorithm also empowers men to have the complete freedom to virtually try different beard styles with the highly sophisticated beard simulation technology, including trim beard, stubble beard, full beard, circle beard, mustache, goatee, and others.

Shoppers can also see before and after results, without the commitment of putting a razor to the skin.
The beard filters include mustache, short box, ducktail, circle and a dozen more.

## Integration Guide

1. **Upload a Selfie**
  You can provide the source image in one of two ways:

  - **Use an Existing Public Image URL**
    Instead of uploading, you may supply a publicly accessible image URL directly when initiating the AI task.

  - **Upload via File API**
    Use the endpoint:
    ```
    POST /s2s/v2.0/file
    ```
    This returns a `file_id` for subsequent task execution.

    - ***Important***: Simply calling the File API does not upload your file. You must **manually upload** the file to the **URL provided in the File API response**. That URL is your upload destination, make sure the file is successfully transferred there before proceeding.<br></br>
    Before calling the AI API, ensure your file has been successfully uploaded. Use the File API to retrieve an upload URL, then upload your file to that location. Once the upload is complete, you'll receive a ***file_id*** in the response, this ID is what you'll use to access AI features related to that file.

      > **Warning:** Please note that, you will get an 500 Server Error / unknown_internal_error or 404 Not Found error when using AI APIs if you do not upload the file to the URL provided in the File API response.

2.  **List Predefined Styles**
    *   Use /s2s/v2.0/task/template/beard-style to fetch a predefined template list and select a ``template_id`` to run an AI task.

3.  **Run an AI Task to Obtain a Task ID**
    Execute the AI task using /s2s/v2.0/task/beard-style. For the target user image, provide either ``src_file_url`` or ``src_file_id``. And a stype ``template_id`` to apply and obtain a ``task_id``.

4.  **Poll to Check the Status of a Task Until It Succeeds or Fails**
    Use the ``task_id`` to monitor the task status by polling GET /s2s/v2.0/task/beard-style to retrieve the current engine status. Until the engine completes the task, the status will remain as running, and no units will be consumed during this stage.
    You can also implement a webhook to receive notifications when an AI task succeeds or fails. Refer to the **[Webhook](../../../develop/webhook)** section for details.

    > **Warning:** Polling to check the status of a task within its retention period is mandatory. A task will time out if there is no polling request within the retention period, even if the task is processed successfully. Your units will still be consumed.

    > **Warning:** You will receive an InvalidTaskId error if you check the status of a timed-out task. Therefore, once you run an AI task, you must poll to check the status within the retention period until the status becomes either success or error.

5.  **Retrieve the Result of an AI Task Once Successful**
    The task status will change to success after the engine processes your input file and generates the resulting image. You will receive a URL for the processed image.

---

## File Specs & Errors
   * Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Beardstyle Generator|Resolution: Long side < 1024 <br>face width > 256 <br>face pose: -30 < yaw < 30, <br>single face only, <br>need to show full face|< 10MB|jpg/jpeg|

   * Error Codes

|Error Code|Description|
|  ----  | ----  |
|error_no_face	|Face are not visible in the source image
|error_src_face_too_small	|The face is too small
|error_inference	|Beard removal error or beard generation error
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

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Beard Style Generator V1.0 | 2 |

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

 - [AI Beard Style Generator](https://docs.perfectcorp.com/_bundle/reference/ai_beard_style.yaml)

## V1.0

 - [GET /s2s/v2.0/task/template/beard-style](https://docs.perfectcorp.com/reference/ai_beard_style/v1.0/paths/~1s2s~1v2.0~1task~1template~1beard-style/get.md)
 - [POST /s2s/v2.0/task/beard-style](https://docs.perfectcorp.com/reference/ai_beard_style/v1.0/paths/~1s2s~1v2.0~1task~1beard-style/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/beard-style/{task_id}](https://docs.perfectcorp.com/reference/ai_beard_style/v1.0/paths/~1s2s~1v2.0~1task~1beard-style~1%7Btask_id%7D/get.md)