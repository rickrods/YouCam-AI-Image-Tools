# AI Hair Color Virtual Try-On

# Overview
Explore a wide range of hair colors with our hair color changer! Try the hair color you've always dreamed of and experiment with new shades you’ve never tried before. Easily adjust the intensity of your chosen color with sliders for a customized look.

   * Upload Your Image

Upload the photo you want to change hair color for.

   * Choose Preset Colors or Customize by Pattern and Palettes

Choose from predefined color presets or fine tune by adjusting the ombre coverage and blend for unlimited possibilities!

> **Warning:** If both a preset and pattern + palettes are specified, the preset will take priority.

> **Warning:** Your source image needs to contain the hair section for dyeing, so double-check before applying. Make sure your source image includes the hair area you want to dye — it's your responsibility to get it right.

![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/hair_color_s2_poster_dt_v2_49198cabc0.png)

![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/01_1_1_8365c3b503.jpg)

![](https://bcw-media.s3.ap-northeast-1.amazonaws.com/01_2_1_abfcdb7eba.jpg)

## File Specs & Errors
* Supported Formats & Dimensions

|AI Feature|Supported Dimensions|Supported File Size|Supported Formats|
|  ----  | ----  | ----  | ----  |
|AI Hair Color|long side < 1920, face width >= 100|< 10MB|jpg/jpeg/png|

* Error Codes

|Error Code|Description|
|  ----  | ----  |
|error_below_min_image_size|the size of the source image is smaller than minimum (expect: width >= 320px, height >= 320px)
|error_exceed_max_image_size|the size of the source image is larger than maximum (expect: width < 1920px, height < 1080px)

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
| AI Hair Color Virtual Try-On V1.0 | 1 unit for Full Mode <br> 1 unit for Ombre Mode |

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

 - [AI Hair Color Virtual Try-On](https://docs.perfectcorp.com/_bundle/reference/ai_hair_color.yaml)

## V1.0

 - [POST /s2s/v2.0/task/hair-color](https://docs.perfectcorp.com/reference/ai_hair_color/v1.0/paths/~1s2s~1v2.0~1task~1hair-color/post.md): This endpoint initiates the hair color change process. You must provide a source file (via URL or File ID) and specify the color settings (preset, pattern, or palettes). The task will be processed asy
 - [GET /s2s/v2.0/task/hair-color/{task_id}](https://docs.perfectcorp.com/reference/ai_hair_color/v1.0/paths/~1s2s~1v2.0~1task~1hair-color~1%7Btask_id%7D/get.md)