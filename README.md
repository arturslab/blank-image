![Laravel debug queries](img/github_header_blank_image.png)

# Blank Image
This class allow you generate "blank" images on your page. It's good for developers for prepare mockup HTML.
For better performance, generated images are stored in cache folder (make sure to write access for cache folder).


## Usage
In your www server place files from this repository:
- **blank_image.php** file
- **cache** folder (make sure for write access)
- **resources** folder (with svg files)

In your HTML add **img** tag with **src** atributes as showing in the **Example** below.

## Example

`<img src="HTTP://DOMAIN.TLD/PATH_TO_THIS_SCRIPT/blank_image.php?height=400&width=600&fillcolor=color3&strokewidth=1&icon=camera">`

![Laravel debug queries](img/github_blank_image_screen.png)

## Parameters
- **height**: height of generated image (in px)
- **width**: width of generated image (in px)
- **fillcolor**: fill color of generated images (values from color1 to color14 or random for random color)
- **strokewidth**: width (in px) for the borders of the generated image
- **icon**: icon dislpayed in center of the blank image. (values:camera, chart, image, player, user. Default: camera)
