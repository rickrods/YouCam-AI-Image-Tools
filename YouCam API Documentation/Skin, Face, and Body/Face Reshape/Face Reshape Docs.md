# AI Face Reshape

# Overview
The AI Face Reshape API lets you programmatically reshape facial features — eyes, nose, lips, jawline, or the whole face — with pixel‑perfect control. Use it to generate before/after visualisations for rhinoplasty, chin fillers, lip augmentations, brow lifts and any other aesthetic‑treatment workflow.

* Rhinoplasty (Nose Job)
Our online rhinoplasty simulator offers medical-grade precision adjustments. Unlike generic photo editing apps, it allows for comprehensive simulation of specific details, including the Bridge, Lift, and Wing. With our hyper-realistic previews, clients can clearly visualize and explore their ideal proportions before consulting.

* Chin Filler
Through our online simulator, you can preview the ideal proportions achieved with chin fillers. Fine-tune Chin Length and Chin Shape to visualize improvements for a receding or short chin. Discover the optimal solution to balance your facial profile before undergoing any dermal filler treatments.

* Lip Filler
Users can experiment with different volumes and shapes of lip fillers to simulate the appearance of fuller lips, helping them decide on the desired outcome before undergoing the procedure.

* Brow Lift Surgery
This functionality enables users to preview the results of a brow lift, which involves lifting and reshaping the eyebrows to create a more youthful and rejuvenated appearance.



## Integration Guide
This guide walks you through:

Workflow for AI Face Reshape API:

**Endpoint:** `/s2s/v2.0/file`

**Authentication Required:** `Authorization: Bearer YOUR_API_KEY`

**Workflow Steps:**

1. **Image Upload Preparation:**
    - Prepare a selfie image for upload.
    - Call the File API `/s2s/v2.0/file` to obtain the upload URL and associated `file_id`.
    - Upload the selfie image using the provided upload URL.

2. **Optional Preprocessing For Multiple Faces:**
    - Preprocess the selfie image if there are more than one face in the image.

3. **Face Reshape Effect Setup:**
   - Begin by selecting suitable face reshape parameters of Eye, Face, Lip or Nose.

4. **Initiate AI Task and Obtain Task ID:**
   - Send the `file_id` along with the chosen effect configuration via an HTTP POST request to `/s2s/v2.0/task/face-reshape`.
   - Await a unique task ID in the response, which identifies this interaction.

5. **Poll Task Status (Continuous Check):**
   - Use the obtained `task_id` to periodically poll the task status using an HTTP GET request (e.g., `GET /s2s/v2.0/task/face-reshape/${task_id}`).
   - Continuously monitor for:
     - `Task_status = "success"` (process completed).
     - `Task_status = "error"` (resolve or retry if applicable).
   - Update the workflow accordingly once the status transitions to success.

This structured workflow ensures efficient integration with user inputs, automated monitoring of tasks, and seamless retrieval of results.

---

* API Playground

Interactively explore and test the API using our official playground:

**API Playground:**
[http://yce.makeupar.com/api-console/en/api-playground/ai-face-reshape/](http://yce.makeupar.com/api-console/en/api-playground/ai-face-reshape/)

---

* Authentication
- Include your API key in the request header using **Bearer Token**:
    ```
    Authorization: Bearer YOUR_API_KEY
    ```
You can find your API Key at https://yce.makeupar.com/api-console/en/api-keys/.


1. Upload an Image

    You may upload a file directly to the server or provide a valid image URL in the AI task payload.

    * Upload Endpoint

        ```
        POST /s2s/v2.0/file
        ```

    Alternatively, skip this step if you already have a public image URL.

---

2. Prepare an effect template

   * Preprocessing
        Output detected bounding boxes in pixel coordinate. Use the index of result to create a Face Reshape AI task later.
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
            "features": {},
            "global": {
            "skin_smooth_strength": 50,
            "skin_smooth_color_intensity": 50,
            },
        }
        ```
        index: index of detected face from preprocessing. optional, default 0.
        features: required at least 1, non-zero face reshape parameter, cannot be all zero.
        skin_smooth_strength: 0~100
        skin_smooth_color_intensity: 0~100

        * Effect Format
        - Face
        default range: -100~100
        range of cheekbones and jaws: 0~100
        all feature values must not be zero at the same time, at least one feature value must be non-zero
        ```
        {
            "cheekbones": 0,
            "jaw": 0,
            "face_reshape_left": 0,
            "face_reshape_right": 0,
            "face_width": 0,
            "chin_reshape_left": 0,
            "chin_reshape_right": 0,
            "chin_length": 0,
        }
        ```

        - Eye
        default range: -100~100
        all feature values must not be zero at the same time, at least one feature value must be non-zero
        ```
        {
            "eye_size_left": 0,
            "eye_size_right": 0,
            "eye_distance": 0,
            "eye_angle": 0,
            "eye_height": 0,
            "eye_width": 0,
        }
        ```

        - Nose
        default range: -100~100
        all feature values must not be zero at the same time, at least one feature value must be non-zero
        ```
        {
            "nose_bridge_width": 0,
            "nose_lift": 0,
            "nose_size": 0,
            "nose_tip": 0,
            "nose_tip_width": 0,
            "nose_wing": 0
        }
        ```

        - Lip
        default range: -100~100
        all feature values must not be zero at the same time, at least one feature value must be non-zero
        ```
        {
            "lip_size": 0,
            "lip_width": 0,
            "lip_peak": 0,
            "lip_height_top": 0,
            "lip_height_bottom": 0,
        }
        ```

        * Example Payload (ready to send)
        ```
        {
        "src_file_url": "https://plugins-media.makeupar.com/strapi/assets/face_reshape_01_85c8ffc055.jpg",
        "version": "1.0",
        "source": "yco",
        "features": {
            "eye_size_left": 80,
            "eye_size_right": 80,
            "eye_width": 0,
            "eye_height": 0,
            "eye_distance": 0,
            "eye_angle": 0,
            "face_reshape_left": 0,
            "face_reshape_right": 0,
            "chin_reshape_left": 20,
            "chin_reshape_right": 20,
            "chin_length": 0,
            "face_width": -30,
            "cheekbones": 0,
            "jaw": 0,
            "lip_size": 10,
            "lip_width": 40,
            "lip_height_top": 10,
            "lip_height_bottom": 10,
            "lip_peak": -10,
            "nose_size": 30,
            "nose_lift": -20,
            "nose_bridge_width": 10,
            "nose_tip": -10,
            "nose_wing": 30,
            "nose_tip_width": 30
        },
        "global": {
            "skin_smooth_strength": 50,
            "skin_smooth_color_intensity": 50
        }
        }
        ```


3. Create a Face Reshape AI Task and Poll for Results

    Once you have an image and a complete effect payload, create a task. The API processes the request asynchronously. You must poll the task status until it reaches `success` or `error`.

   * Create Task Endpoint

        ```
        POST /s2s/v2.0/task/face-reshape
        ```

   * Polling Endpoint

        ```
        GET /s2s/v2.0/task/face-reshape/{task_id}
        ```

---

## File Specs & Errors

* AI Face Reshape Specification

**Supported Selfie View**
A selfie with width and height of a face larger than 1/20 of image width and heigh.
Face angle less than 30 degree for pitch, yaw and rolling.

![](https://plugins-media.makeupar.com/strapi/assets/thumbnail_face_reshape_01_85c8ffc055.jpg)



**Facial Customization Parameters Guide**

| Category | Parameter        | Function                             | Min (-100 / 0) | Max (100)    |
| -------- | ---------------- | ------------------------------------ | -------------- | ------------ |
| Eyes     | Size (L/R)       | Scales overall size of each eye      | Small          | Large        |
| Eyes     | Width            | Adjusts horizontal span              | Narrow         | Wide         |
| Eyes     | Height           | Adjusts vertical span                | Narrow / Flat  | Round / Tall |
| Eyes     | Distance         | Adjusts spacing between eyes         | Close-set      | Wide-set     |
| Eyes     | Angle            | Adjusts rotational tilt              | Inward tilt    | Outward tilt |
| Face     | Size (L/R)       | Scales size of each side of the face | Small          | Large        |
| Face     | Chin Shape (L/R) | Adjusts chin contour width           | Narrow         | Wide         |
| Face     | Chin Length      | Adjusts vertical chin length         | Short          | Long         |
| Face     | Width            | Adjusts overall facial width         | Narrow         | Wide         |
| Face     | Cheekbone        | Adjusts cheekbone prominence         | Original (0)   | Tucked in    |
| Face     | Jaw              | Adjusts jawline prominence           | Original (0)   | Tucked in    |
| Lips     | Size             | Scales overall lip volume            | Small          | Large        |
| Lips     | Width            | Adjusts horizontal span              | Narrow         | Wide         |
| Lips     | Upper Height     | Adjusts top lip thickness            | Thin           | Full         |
| Lips     | Lower Height     | Adjusts bottom lip thickness         | Thin           | Full         |
| Lips     | Peak             | Adjusts Cupid's bow sharpness        | Smooth         | Defined      |
| Nose     | Size             | Scales overall nose size             | Small          | Large        |
| Nose     | Lift             | Adjusts vertical position            | Low            | High         |
| Nose     | Bridge           | Adjusts bridge width                 | Narrow         | Wide         |
| Nose     | Tip              | Adjusts vertical angle of the tip    | Up             | Down         |
| Nose     | Wing             | Adjusts nostril width                | Narrow         | Wide         |
| Nose     | Width            | Adjusts width of the nose tip        | Narrow         | Wide         |

**Note:** *“Left” and “Right” refer to the character's perspective, not the viewer's side of the screen.*


---

* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Face Reshape|long side <= 4096|< 10MB|jpg/jpeg/png|

* Error Codes

| Error Code | Description |
|  ----  | ----  |
| RUNTIME_ERROR | An unexpected error occurred duface reshape runtime |
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
| AI Face Reshape V1.0 | 1 |

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

 - [AI Face Reshape](https://docs.perfectcorp.com/_bundle/reference/ai_face_reshape.yaml)

## V1.0

 - [POST /s2s/v2.0/task/face-reshape/pre-process](https://docs.perfectcorp.com/reference/ai_face_reshape/v1.0/paths/~1s2s~1v2.0~1task~1face-reshape~1pre-process/post.md): Use the pre-process task when the source image may contain more than one valid target, or when your integration needs to explicitly choose which detected target receives the effect. For single-target
 - [GET /s2s/v2.0/task/face-reshape/pre-process/{task_id}](https://docs.perfectcorp.com/reference/ai_face_reshape/v1.0/paths/~1s2s~1v2.0~1task~1face-reshape~1pre-process~1%7Btask_id%7D/get.md): Use the pre-process task when the source image may contain more than one valid target, or when your integration needs to explicitly choose which detected target receives the effect. For single-target
 - [POST /s2s/v2.0/task/face-reshape](https://docs.perfectcorp.com/reference/ai_face_reshape/v1.0/paths/~1s2s~1v2.0~1task~1face-reshape/post.md): AI tasks are asynchronous. Prefer webhook-based completion handling when the feature supports webhooks. Configure your webhook endpoint, verify webhook signatures, and use the received `task_id` to qu
 - [GET /s2s/v2.0/task/face-reshape/{task_id}](https://docs.perfectcorp.com/reference/ai_face_reshape/v1.0/paths/~1s2s~1v2.0~1task~1face-reshape~1%7Btask_id%7D/get.md)