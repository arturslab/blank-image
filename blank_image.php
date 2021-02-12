<?php
/**
 * This class allow you generate "blank" images on your page. It's good for developers for prepare mockup HTML.
 * For better performance, generated images are stored in cache folder (make sure to write access for cache folder)
 *
 * How to use:
 * In your HTML add src atributes as showing in Example below.
 *
 * Example:
 * HTTP://DOMAIN.TLD/PATH_TO_THIS_SCRIPT/blank_image.php?height=400&width=600&fillcolor=color3&strokewidth=1&icon=camera
 *
 * Parameters:
 * height: height of generated image (in px)
 * width: width of generated image (in px)
 * fillcolor: fill color of generated images (values from color1 to color14 or random for random color)
 * strokewidth: width (in px) for the borders of the generated image
 * icon: icon dislpayed in center of the blank image. (values:camera, chart, image, player, user. Default: camera)
 *
 */

// blank_image.php?height=400&width=600&fillcolor=color4&strokewidth=3&icon=camera

$args = [
    'height' => isset($_GET['height']) ? $_GET['height'] : 100,
    'width' => isset($_GET['width']) ? $_GET['width'] : 100,
    'fillColor' => isset($_GET['fillcolor']) ? $_GET['fillcolor'] : null,
    'strokeWidth' => isset($_GET['strokewidth']) ? $_GET['strokewidth'] : null,
    'strokeColor' => isset($_GET['strokecolor']) ? $_GET['strokecolor'] : null,
    'icon' => isset($_GET['icon']) ? $_GET['icon'] : null,
];


class BlankImage
{

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $resourcesDir = '/resources/';

    /**
     * @var string
     */
    private $cacheDir = '/cache/';

    /**
     * @var bool
     */
    private $overridaCache = false;

    /**
     * @var array
     */
    private $colors = [
        'color1' => '#ffffff',
        'color2' => '#67dab8',
        'color3' => '#61c0cf',
        'color4' => '#af83f8',
        'color5' => '#e374ab',
        'color6' => '#e77681',
        'color7' => '#fea75f',
        'color8' => '#424242',
        'color9' => '#6dc381',
        'color10' => '#03a9f4',
        'color11' => '#ff9800',
        'color12' => '#cddc39',
        'color13' => '#ff5722',
        'color14' => '#9e9e9e',
    ];

    /**
     * @var array
     */
    private $icons = [
        'camera' => 'camera.svg',
        'chart' => 'chart.svg',
        'image' => 'image.svg',
//        'map_mark' => 'map_mark_opt.svg',
        'player' => 'player.svg',
        'user' => 'user.svg',
    ];

    /**
     * @var array
     */
    private $extensions = ['png', 'jpeg', 'jpg', 'gif'];

    public function __construct()
    {
        $this->options = [
            'height' => null,
            'width' => null,
            'fillColor' => null,
            'strokeColor' => null,
            'strokeWidth' => null,
            'icon' => null,
            'resourcesPath' => getcwd() . $this->resourcesDir,
            'cachePath' => getcwd() . $this->cacheDir,
        ];
    }


    public function showImage()
    {
        if ($this->validateConfiguration()) {
            $this->filename = $this->getFilename();

            if (!$this->overrideCache && $this->getCacheImage()) {
            } else {
                $this->writeCacheImage();
            }
        } else {
            http_response_code(404);
            die();
        }
    }

    /**
     * @param array $args
     */
    public function configure(array $args)
    {
        // Set sizes
        $width = $args['width'] > 9 ? (int)$args['width'] : 10;
        $height = $args['height'] > 9 ? (int)$args['height'] : 10;
        $strokeWidth = $args['strokeWidth'] > 0 ? (int)$args['strokeWidth'] : 1;

        if (!isset($args['icon'])) {
            $args['icon'] = null;
        }

        $minSide = min([$width, $height]);

        $strokeWidth = $strokeWidth < $minSide ? (int)$strokeWidth : 2;

        // Random or specific color
        $fillColor = array_key_exists($args['fillColor'], $this->colors) ? $this->colors[$args['fillColor']] : ($args['fillColor'] == 'random' ? array_values($this->colors)[rand(0,
            count($this->colors) - 1)] : array_values($this->colors)[0]);

        // Random or specific icon file
        $icon = array_key_exists($args['icon'], $this->icons) ? $this->icons[$args['icon']] : ($args['icon'] == 'random' ? array_values($this->icons)[rand(0,
            count($this->icons) - 1)] : array_values($this->icons)[0]);

        $this->setOption('width', $width);
        $this->setOption('height', $height);
        $this->setOption('ratio', round($height / $width, 2));
        $this->setOption('strokeWidth', $strokeWidth);
        $this->setOption('iconFile', $this->getIconFile($icon));
        $this->setOption('minSide', $minSide);
        $this->setOption('strokeWidth', $strokeWidth);
        $this->setOption('strokeColor', 'black');
        $this->setOption('imageBackground', 'silver');
        $this->setOption('outputImageExt', 'png');
        $this->setOption('fillColor', $fillColor);
    }

    public function overrideCache()
    {
        $this->overrideCache = true;
    }

    public function clearCachedFiles()
    {
    }

    /**
     * @return string|null
     */
    private function getContentType()
    {
        $ctype = null;
        if ($this->getOption('outputImageExt') && in_array($this->getOption('outputImageExt'), $this->extensions)) {
            switch ($this->getOption('outputImageExt')) {
                case "gif":
                    $ctype = "image/gif";
                    break;
                case "png":
                    $ctype = "image/png";
                    break;
                case "jpeg":
                case "jpg":
                    $ctype = "image/jpeg";
                    break;
                case "svg":
                    $ctype = "image/svg+xml";
                    break;
                default:
            }
        }

        return $ctype;
    }


    /**
     * @param string|null $name
     * @return string|null
     */
    private function getIconFile(?string $name): ?string
    {
        return $name && file_exists($this->getOption('resourcesPath') . $name) ? $this->getOption('resourcesPath') . $name : null;
    }

    private function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    private function getOption(string $name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * @return bool
     */
    private function validateConfiguration(): bool
    {
        return $this->options
            && isset($this->options['width'], $this->options['height'], $this->options['strokeWidth'], $this->options['minSide'], $this->options['strokeColor'], $this->options['fillColor'], $this->options['imageBackground'], $this->options['outputImageExt'])
            && $this->options['width'] > 0
            && $this->options['height'] > 0
            && $this->options['strokeWidth'] > 0
            && $this->options['minSide'] > 0
            && $this->options['strokeColor']
            && $this->options['fillColor']
            && $this->options['imageBackground']
            && $this->options['outputImageExt'];
    }

    /**
     * @return string
     */
    private function getFilename(): string
    {
        return md5(serialize($this->options));
    }

    /**
     * @return bool
     * @throws ImagickException
     */
    private function writeCacheImage(): bool
    {
        // Draw main image
        $imagick = new \Imagick();
        $imagick->newImage($this->getOption('width'), $this->getOption('height'), $this->getOption('imageBackground'));
        $imagick->setImageFormat($this->getOption('outputImageExt'));

        // Draw borders on the main image file
        $draw = new \ImagickDraw();
        $strokeColor = new \ImagickPixel($this->getOption('strokeColor'));
        $fillColor = new \ImagickPixel($this->getOption('fillColor'));

        $draw->setStrokeColor($strokeColor);
        $draw->setFillColor($fillColor);
        $draw->setStrokeOpacity(0.3);
        $draw->setStrokeWidth($this->getOption('strokeWidth'));
        $draw->rectangle(0, 0, $this->getOption('width'), $this->getOption('height'));

        $imagick->drawImage($draw);

        // Draw icon on the main image file with the given coordinates
        if ($this->getOption('iconFile')) {
            $icon = new \Imagick();

            $icon->setBackgroundColor(new ImagickPixel('transparent'));

            // Set bigger resolution for SVG file before rasterize image - for better quality effects
            $icon->setResolution(5000, 5000);
            $icon->readImage($this->getOption('iconFile'));
            $iconWidth = $icon->getImageWidth();
            $iconHeight = $icon->getImageHeight();

            $imageWidth = $this->getOption('width');
            $imageHeight = $this->getOption('height');

            // Resize the icon
            $icon->scaleImage($imageWidth * 0.5, $imageHeight * 0.5, true);

            if ($imageHeight < $iconHeight || $imageWidth < $iconWidth) {
                // Get new size
                $iconWidth = $icon->getImageWidth();
                $iconHeight = $icon->getImageHeight();
            }

            // Calculate the position
            $x = ($imageWidth - $iconWidth) / 2;
            $y = ($imageHeight - $iconHeight) / 2;


            $icon->setImageFormat("png24");

            $imagick->compositeImage($icon, Imagick::COMPOSITE_OVER, $x, $y);
        }


        if ($ctype = $this->getContentType()) {
            header("Content-Type: $ctype");
        }
        echo $imagick->getImageBlob();

        return $imagick->writeImage($this->getOption('cachePath') . $this->filename . '.' . $this->getOption('outputImageExt')); // fails with no error message
    }

    private function getCacheImage()
    {
        $filePath = $this->getOption('cachePath') . $this->filename . '.' . $this->getOption('outputImageExt');
        if (file_exists($filePath)) {
            // read from cache
            if ($ctype = $this->getContentType()) {
                $fileData = file_get_contents($filePath);
                header("Content-Type: $ctype");
                echo $fileData;
            }
        } else {
            return false;
        }
    }


    public static function d($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

}

$image = new BlankImage();
$image->configure($args);
//$image->overrideCache();
$image->showImage();