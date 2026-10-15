# AI Face Swap

# Overview
Using AI Face Swap for hyper-realistic effect with multiple faces supported.​ Our face swap artificial intelligence supports swapping one or multiple faces. Either for creating funny pictures of faces, or need a professional tool, we've got you covered.

## Integration Guide
* How to implement AI Face Swap

   * Step 1: Upload source and reference images

      1. Request upload URLs from the API:

        ```
        POST https://yce-api-01.makeupar.com/s2s/v2.0/file
        Authorization: Bearer YOUR_API_KEY
        Content-Type: application/json
        ```

        Body:

        ```json
        {
            "files": [
            {
                "file_name": "target.jpg",
                "file_size": 123456,
                "content_type": "image/jpeg"
            }
            ]
        }
        ```
        1. The response provides a pre-signed **upload URL** and a `file_id`.
        2. Upload your file with an HTTP PUT request to the given URL.
        3. Store the `file_id` for later use. Repeat this for both **target** and **reference** images.

      2. Upload the actual file to the **upload URL**.

---

   * Step 2: Pre-process the source and reference images (face detection)

        1. Create a pre-process task:

        ```
        POST https://yce-api-01.makeupar.com/s2s/v2.0/task/face-swap/pre-process
        Authorization: Bearer YOUR_API_KEY
        Content-Type: application/json
        ```

        Body:

        ```json
        {
            "request_id": 1,
            "payload": {
            "file_sets": {
                "src_ids": ["TARGET_FILE_ID"]
            },
            "actions": [
                { "id": 0 }
            ]
            }
        }
        ```
        1. The API returns a `task_id`.
        2. Poll task status at:

        ```
        GET https://yce-api-01.makeupar.com/s2s/v2.0/task/face-swap/pre-process?task_id=TASK_ID
        ```
        1. When finished, you receive a list of detected faces with bounding boxes.

---

   * Step 3: Run the face swap task
        1. Define which reference image will substitute each source image
        The `face_mapping` array defines how faces in the **Source Image** are replaced by faces from the **Reference Images**. It acts as a link list connecting detected faces in the source to specific reference images.

        * Structure

            Each element in the array is an object containing two properties:

            | Parameter | Type | Description |
            | :--- | :--- | :--- |
            | `position` | `integer` | The index of the face detected in the **Source Image** (e.g., 0, 1, 2). |
            | `index` | `integer` | The index of the face image in the **Reference Image List** to swap with. |

                * Logic Rules
            1.  **Index Mapping:** The `index` maps directly to the order of images provided in your reference list.
                *   `0`: First Reference Image.
                *   `1`: Second Reference Image.
            2.  **Skipping Swaps:** To skip swapping a specific face detected in the source, set both `index` and `position` to `-1`.
            3.  **Array Order:** The order of objects in the array should match based on `position`.

                * Example Use Case

            **Scenario:**
            *   **Reference List:** 2 images provided (Image A, Image B).
            *   **Source Image:** Contains 3 faces detected (Face 0, Face 1, Face 2).

            **Goal:**
            *   Swap **Face 0** (Source) with **Image 1** (Reference).
            *   Skip swapping **Face 1** (Source).
            *   Swap **Face 2** (Source) with **Image 0** (Reference).

            **Configuration:**

            ```json
            "face_mapping": [
                {
                    "index": 1,  // Use the second reference image
                    "position": 0 // Apply to the first detected face in source
                },
                {
                    "index": -1, // Skip swapping
                    "position": -1 // Skip swapping
                },
                {
                    "index": 0,  // Use the first reference image
                    "position": 2 // Apply to the third detected face in source
                }
            ]
            ```

        1. Send the main task request:

        ```
        POST https://yce-api-01.makeupar.com/s2s/v2.0/task/face-swap
        Authorization: Bearer YOUR_API_KEY
        Content-Type: application/json
        ```

        Body:

        ```json
        {
            "request_id": 2,
            "payload": {
            "file_sets": {
                "src_ids": ["TARGET_FILE_ID"],
                "ref_ids": ["REFERENCE_FILE_ID"]
            },
            "actions": [
                {
                "id": 0,
                "params": {
                    "face_mapping": [
                    { "index": 0, "position": 0 },
                    { "index": -1, "position": -1 }
                    ]
                }
                }
            ]
            }
        }
        ```
        1. The response returns a `task_id`.

---

   * Step 4: Poll task status and retrieve result
        It’s necessary to implement a timed loop that queries the task status at regular intervals within the allowed polling window.
        1. Poll at:

        ```
        GET https://yce-api-01.makeupar.com/s2s/v2.0/task/face-swap?task_id=TASK_ID
        ```
        2. When `status` is `success`, the response contains a URL for the generated image.
        3. Download or display the image from that URL.

---

   * Step 5: Integrate into your platform

        * On a **web frontend**, you can directly implement this with JavaScript using fetch or Axios.
        * On a **backend** (Node.js, Python, Java, PHP, etc.), you can use the same endpoints with standard HTTP libraries.
        * Implement retry and error handling since the tasks run asynchronously.

---

    * Debugging Guide
        1. **Invalid TaskId Error**
            </br>**Why:** You’ll receive an InvalidTaskId error if you attempt to check the status of a task that has timed out. Therefore, once an AI task is initiated, you’ll need to poll for its status within the polling_interval until the status changes to either success or error.
            </br>**Solution:** To avoid the task becoming invalid, it’s necessary to implement a timed loop that queries the task status at regular intervals within the allowed polling window.

        2. **Why are some faces not detected in my source image**
            </br>**Why:** Reason: The face must be clearly visible, not covered or obstructed, and large enough within the image
            </br>**Solution:** Try taking a photo where the face appears larger and is clearly visible without any covering or obstruction

---

## Inputs & Outputs

* Real-world examples:
Multiple faces swap sample:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/dt_yce_face_swap_S2_img_04_d4b747a41d.jpg)

Single face swap sample:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/dt_yce_face_swap_S2_img_05_8e68faff2c.jpg)

* Suggestions for How to Shoot:
![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/strapi/assets/webp_AI%20Skin%20Analysis_camera_f93315b088.png)


## File Specs & Errors
* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Face Swap|Input and output: the long side must be less than or equal to 4096 pixels|< 10MB|jpg/jpeg/png|


* Error Codes

| Error Code | Description |
| ------------------ | ----------- |
| exceed_max_filesize | The input file size exceeds the maximum limit |
| invalid_parameter | The parameter value is invalid |
| error_download_image | There was an error downloading the source image |
| error_download_mask | There was an error downloading the mask image |
| error_decode_image | There was an error decoding the source image |
| error_decode_mask | There was an error decoding the mask image |
| error_download_video | There was an error downloading the source video |
| error_decode_video | There was an error decoding the source video |
| error_nsfw_content_detected | NSFW content was detected in the source image |
| error_no_face | No face was detected in the source image |
| error_pose | Failed to detect pose in the source image |
| error_face_parsing | Failed to perform face parsing on the source image |
| error_inference | An error occurred in the inference pipeline |
| exceed_nsfw_retry_limits | Retry limits exceeded to avoid generating NSFW image |
| error_upload | There was an error uploading the result image |
| error_multiple_people | People count exceeds the maximum limit |
| error_no_shoulder | Shoulders are not visible in the source image |
| error_large_face_angle | The face angle in the uploaded image is too large |
| error_unsupport_ratio | The aspect ratio of the input image is unsupported |
| unknown_internal_error | Other internal errors |

---

## Unit Consumption

| AI Feature | Unit Consumed |
|---|---|
| AI Face Swap V1.0 | 1 |

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

 - [AI Face Swap](https://docs.perfectcorp.com/_bundle/reference/ai_face_swap.yaml)

## V1.0

 - [POST /s2s/v2.0/task/face-swap/pre-process](https://docs.perfectcorp.com/reference/ai_face_swap/v1.0/paths/~1s2s~1v2.0~1task~1face-swap~1pre-process/post.md): Use the pre-process task when the source image may contain more than one valid target, or when your integration needs to explicitly choose which detected target receives the effect. For single-target
 - [GET /s2s/v2.0/task/face-swap/pre-process/{task_id}](https://docs.perfectcorp.com/reference/ai_face_swap/v1.0/paths/~1s2s~1v2.0~1task~1face-swap~1pre-process~1%7Btask_id%7D/get.md)
 - [POST /s2s/v2.0/task/face-swap](https://docs.perfectcorp.com/reference/ai_face_swap/v1.0/paths/~1s2s~1v2.0~1task~1face-swap/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/face-swap/{task_id}](https://docs.perfectcorp.com/reference/ai_face_swap/v1.0/paths/~1s2s~1v2.0~1task~1face-swap~1%7Btask_id%7D/get.md)