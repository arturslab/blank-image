<?php
/**
 * This class allow you generate "blank" images on your page. It's good for developers for prepare mockup HTML.
 * For better performance, generated images are stored in cache folder (make sure to write access for cache folder)
 *
 * How to use:
 * In your HTML add src attributes as showing in Example below.
 *
 * Example:
 * <img src="HTTP://DOMAIN.TLD/PATH_TO_THIS_SCRIPT/blank_image.php?height=300&width=300&fillcolor=3&strokewidth=25&icon=user&font=random&text=abcd" alt="Sample image">
 *
 * Parameters:
 * height: height of generated image (in px)
 * width: width of generated image (in px)
 * fillcolor: fill color of generated images
 * strokewidth: width (in px) for the borders of the generated image
 * icon: icon showing in center of the blank image.
 * text: you can set custom text label in blank image
 *
 * For more information, see README.md file
 *
 */

class BlankImage {

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
	private $overwriteCache = false;

	/**
	 * @var string
	 */
	private $version = '1.2.1';

	/**
	 * @var array
	 */
	private $colors = [
		'#ffffff',
		'#67dab8',
		'#61c0cf',
		'#af83f8',
		'#e374ab',
		'#e77681',
		'#fea75f',
		'#424242',
		'#6dc381',
		'#03a9f4',
		'#ff9800',
		'#cddc39',
		'#ff5722',
		'#9e9e9e',
	];

	/**
	 * @var array
	 */
	private $icons = [
		'calendar'  => 'calendar_opt.svg',
		'camera'    => 'camera_opt.svg',
		'chart'     => 'chart_opt.svg',
		'cart'      => 'cart_opt.svg',
		'heart'     => 'heart_opt.svg',
		'hourglass' => 'hourglass_opt.svg',
		'image'     => 'image_opt.svg',
		'map_mark'  => 'map_mark_opt.svg',
		'player'    => 'player_opt.svg',
		'store'     => 'store.svg',
		'user'      => 'user_opt.svg',
	];

	/**
	 * @var array
	 */
	private $fonts = [
		'abril'         => 'AbrilFatface-Regular.ttf',
		'anton'         => 'Anton-Regular.ttf',
		'bangers'       => 'Bangers-Regular.ttf',
		'boogaloo'      => 'Boogaloo-Regular.ttf',
		'carterone'     => 'CarterOne-Regular.ttf',
		'dancingscript' => 'DancingScript-VariableFont_wght.ttf',
		'fredericka'    => 'FrederickatheGreat-Regular.ttf',
		'indieflower'   => 'IndieFlower-Regular.ttf',
		'luckiestguy'   => 'LuckiestGuy-Regular.ttf',
		'oswald'        => 'Oswald-VariableFont_wght.ttf',
		'righteous'     => 'Righteous-Regular.ttf',
		'roboto'        => 'Roboto-Regular.ttf',
	];

	/**
	 * @var array
	 */
	private $extensions = [ 'png', 'jpeg', 'jpg', 'gif' ];

	public function __construct() {
		$this->options = [
			'height'        => null,
			'width'         => null,
			'fillColor'     => null,
			'strokeColor'   => null,
			'strokeWidth'   => null,
			'icon'          => null,
			'resourcesPath' => getcwd() . $this->resourcesDir,
			'cachePath'     => getcwd() . $this->cacheDir,
		];
	}

	public function showImage(): void {
		if ( $this->validateConfiguration() ) {
			$this->filename = $this->getFilename();

			$this->getCacheImage();
		} else {
			http_response_code( 404 );
			die();
		}
	}

	/**
	 * @throws ImagickException
	 */
	public function showHelpImage() {
		header( "Content-Type: image/png" );

		echo $this->getHelpImage();
	}

	/**
	 * @return string a string containing the image
	 * @throws ImagickException
	 */
	public function getHelpImage(): string {
		$default['background']   = new \ImagickPixel( '#242729ff' );
		$default['headerColor']  = new \ImagickPixel( '#00bcd4' );
		$default['infoColor']    = new \ImagickPixel( '#d8d8d8' );
		$default['primaryColor'] = '#cddc39';
		$default['fontSize']     = 16;
		$default['imageWidth']   = 900;
		$default['imageHeight']  = 1300;
		$default['itemsPadding'] = 20;
		$default['defaultFont']  = $this->getFontFile( array_values( $this->fonts )[9] );

		$params = [
			'height'      => 300,
			'width'       => 400,
			'fillcolor'   => 'color1',
			'strokewidth' => 25,
			'icon'        => array_key_first( $this->icons ),
			'font'        => array_key_first( $this->fonts ),
			'text'        => 'Lorem ipsum',
		];

		$url        = self::getServerUrl();
		$exampleUrl = $url . '?' . http_build_query( $params );

		// Draw main image
		$image = new \Imagick();
		$image->newImage( $default['imageWidth'], $default['imageHeight'], $default['background'] );
		$image->setImageFormat( 'png' );

		$drawText = new ImagickDraw();
		$drawText->setFillColor( $default['infoColor'] );
		$drawText->setFont( $default['defaultFont'] );
		$drawText->setFontSize( $default['fontSize'] );
		$x     = 10;
		$y     = 20;
		$angle = 0;

		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'Parameters for blank image' );
		$y = $y + $default['itemsPadding'];

		// Print size
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'width:' );
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['infoColor'] );
		$image->annotateImage( $drawText, $x + $default['itemsPadding'], $y, $angle, '- number (pixels)' );
		$y = $y + $default['itemsPadding'];

		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'height:' );
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['infoColor'] );
		$image->annotateImage( $drawText, $x + $default['itemsPadding'], $y, $angle, '- number (pixels)' );
		$y = $y + $default['itemsPadding'];

		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'strokewidth:' );
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['infoColor'] );
		$image->annotateImage( $drawText, $x + $default['itemsPadding'], $y, $angle, '- number (pixels) - width of image border' );
		$y = $y + $default['itemsPadding'];

		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'text:' );
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['infoColor'] );
		$image->annotateImage( $drawText, $x + $default['itemsPadding'], $y, $angle, '- text on image' );
		$y = $y + $default['itemsPadding'];

		// Print colors
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'fillcolor:' );
		$y = $y + $default['itemsPadding'];

		$countItems = count( $this->colors );
		$columns    = 3;
		$rows       = ceil( $countItems / $columns );
		$i          = 0;
		$xItem      = $x + $default['itemsPadding'];
		$yItem      = $y;

		$drawRectangle = new \ImagickDraw();
		$drawRectangle->setStrokeColor( 'black' );
		$rectangleWidth = 20;
		foreach ( $this->colors as $k => $v ) {
			if ( $i == $rows ) {
				$i     = 0;
				$xItem = $xItem + floor( $default['imageWidth'] / $columns ) - $default['itemsPadding'];
				$yItem = $y;
			}

			$drawRectangle->setFillColor( new \ImagickPixel( $v ) );
			$drawRectangle->setStrokeOpacity( 1 );
			$drawRectangle->setStrokeWidth( 1 );
			$drawRectangle->rectangle( $xItem, $yItem - $rectangleWidth + $default['itemsPadding'], $xItem + $rectangleWidth, $yItem + $default['itemsPadding'] );

			$image->drawImage( $drawRectangle );

			$drawText->setFillColor( new \ImagickPixel( $v ) );

			$image->annotateImage( $drawText, $xItem + $default['itemsPadding'] + $rectangleWidth, $yItem + $default['itemsPadding'], $angle, $k );

			$yItem = $yItem + $default['itemsPadding'] * 2;

			$i ++;
		}
		$y = $yItem + $default['itemsPadding'] * 2;

		// Print fonts
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'font:' );
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['infoColor'] );

		$countItems = count( $this->icons );
		$columns    = 3;
		$rows       = ceil( $countItems / $columns );
		$i          = 0;
		$xItem      = $x + 5;
		$yItem      = $y + $default['itemsPadding'];

		foreach ( $this->fonts as $k => $v ) {
			if ( $i == $rows ) {
				$i     = 0;
				$xItem = $xItem + floor( $default['imageWidth'] / $columns ) - $default['itemsPadding'];
				$yItem = $y + $default['itemsPadding'];
			}

			$drawText->setFont( $this->getFontFile( $v ) );
			$drawText->setFontSize( $default['fontSize'] * 1.4 );
			$drawText->setFillColor( $default['primaryColor'] );

			// Font style
			$image->annotateImage( $drawText, $xItem + $default['itemsPadding'], $yItem, $angle, 'Sample' );

			// Label
			$drawText->setFont( $default['defaultFont'] );
			$drawText->setFontSize( $default['fontSize'] );
			$drawText->setFillColor( $default['infoColor'] );
			$image->annotateImage( $drawText, $xItem + $default['itemsPadding'] + 120, $yItem, $angle, $k );

			$yItem = $yItem + $default['itemsPadding'] + 20;

			$i ++;
		}
		$y = $yItem + $default['itemsPadding'];
		$drawText->setFontSize( $default['fontSize'] );

		// Print icons
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$image->annotateImage( $drawText, $x, $y, $angle, 'icon:' );
		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['infoColor'] );

		$icon       = new \Imagick();
		$countItems = count( $this->icons );
		$columns    = 4;
		$rows       = ceil( $countItems / $columns );
		$i          = 0;
		$xItem      = $x + 5;
		$yItem      = $y;
		$iconHeight = 0;

		foreach ( $this->icons as $k => $v ) {
			if ( $i == $rows ) {
				$i     = 0;
				$xItem = $xItem + floor( $default['imageWidth'] / $columns ) - $default['itemsPadding'];
				$yItem = $y;
			}
			$icon->setBackgroundColor( new \ImagickPixel( 'transparent' ) );
			// Set bigger resolution for SVG file before rasterize image - for better quality effects
			$icon->setResolution( 5000, 5000 );
			$icon->readImage( $this->getIconFile( $v ) );
			$iconWidth  = $icon->getImageWidth();
			$iconHeight = $icon->getImageHeight();

			// Resize the icon
			$icon->scaleImage( ( $default['imageWidth'] / ( $columns * 2 ) ) - $default['itemsPadding'], ( $default['imageHeight'] / ( $columns * 2 ) ) - $default['itemsPadding'], true );

			if ( $default['imageHeight'] < $iconHeight || $default['imageWidth'] < $iconWidth ) {
				// Get new size
				$iconWidth  = $icon->getImageWidth();
				$iconHeight = $icon->getImageHeight();
			}

			// Calculate the position
			$icon->setImageFormat( "png24" );
			$image->compositeImage( $icon, Imagick::COMPOSITE_OVER, $xItem, $yItem );

			// Label
			$image->annotateImage( $drawText, $xItem + $iconWidth + $default['itemsPadding'], $yItem + $iconHeight / 2, $angle, $k );

			$yItem = $yItem + $iconHeight + $default['itemsPadding'];

			$i ++;
		}
		$y = $yItem + $default['itemsPadding'] + $iconHeight;

		$y = $y + $default['itemsPadding'];
		$drawText->setFillColor( $default['headerColor'] );
		$drawText->setFontSize( $default['fontSize'] * 0.7 );
		$image->annotateImage( $drawText, $x, $y, $angle, '____________________' );
		$y = $y + $default['itemsPadding'];
		$image->annotateImage( $drawText, $x, $y, $angle, 'Example: ' . $exampleUrl );
		$y = $y + $default['itemsPadding'];
		$image->annotateImage( $drawText, $x, $y, $angle, 'BlankImage ver. ' . $this->version );

		return $image->getImageBlob();
	}

	/**
	 * @param array $args
	 *
	 * @throws Exception
	 */
	public function configure( array $args ): void {
		array_walk_recursive( $args, static function ( &$v ) {
			$v = htmlentities( strip_tags( urlencode( trim( $v ) ) ) );
		} );

		// Set sizes
		$width       = $args['width'] > 9 ? (int) $args['width'] : 150;
		$height      = $args['height'] > 9 ? (int) $args['height'] : 150;
		$strokeWidth = $args['strokeWidth'] ? (int) $args['strokeWidth'] : 0;

		if ( ! isset( $args['icon'] ) ) {
			$args['icon'] = null;
		}

		$text = ! isset( $args['text'] ) ? null : self::validateString( $args['text'] );

		$minSide = min( [ $width, $height ] );

		$strokeWidth = $strokeWidth < $minSide ? $strokeWidth : 2;

		// Random or specific color
		$fillColor = array_key_exists( $args['fillColor'], $this->colors ) ? $this->colors[ $args['fillColor'] ] : ( $args['fillColor'] === 'random' ? array_values( $this->colors )[ random_int( 0,
			count( $this->colors ) - 1 ) ] : array_values( $this->colors )[0] );

		// Random or specific font
		$font = array_key_exists( $args['font'], $this->fonts ) ? $this->fonts[ $args['font'] ] : ( $args['font'] === 'random' ? array_values( $this->fonts )[ random_int( 0,
			count( $this->fonts ) - 1 ) ] : array_values( $this->fonts )[0] );

		// Random or specific icon file
		$icon = $args['icon'] && array_key_exists( $args['icon'], $this->icons ) ? $this->icons[ $args['icon'] ] : ( $args['icon'] === 'random' ? array_values( $this->icons )[ random_int( 0,
			count( $this->icons ) - 1 ) ] : null );

		$this->setOption( 'width', $width );
		$this->setOption( 'height', $height );
		$this->setOption( 'ratio', round( $height / $width, 2 ) );
		$this->setOption( 'fillColor', $fillColor );
		$this->setOption( 'strokeWidth', $strokeWidth );
		$this->setOption( 'strokeColor', 'black' );
		$this->setOption( 'iconFile', $this->getIconFile( $icon ) );
		$this->setOption( 'minSide', $minSide );
		$this->setOption( 'text', $text );
		$this->setOption( 'fontFile', $this->getFontFile( $font ) );
		$this->setOption( 'outputImageExt', 'png' );
	}

	public function overwriteCache(): void {
		$this->overwriteCache = true;
	}

	/**
	 * Remove all image files from cache folder
	 */
	public function clearCache(): void {
		$ext = implode( ',', $this->extensions );

		if ( $ext ) {
			$files = glob( getcwd() . $this->cacheDir . '*.{' . $ext . '}', GLOB_BRACE );

			if ( $files ) {
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						unlink( $file );
					}
				}
			}
		}
		die();
	}

	/**
	 * @return string|null
	 */
	private function getContentType(): ?string {
		$ctype = null;
		if ( $this->getOption( 'outputImageExt' ) && in_array( $this->getOption( 'outputImageExt' ), $this->extensions ) ) {
			switch ( $this->getOption( 'outputImageExt' ) ) {
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
	 *
	 * @return string|null
	 */
	private function getIconFile( ?string $name ): ?string {
		return $name && file_exists( $this->getOption( 'resourcesPath' ) . 'svg/' . $name ) ? $this->getOption( 'resourcesPath' ) . 'svg/' . $name : null;
	}

	/**
	 * @param string|null $name
	 *
	 * @return string|null
	 */
	private function getFontFile( ?string $name ): ?string {
		return $name && file_exists( $this->getOption( 'resourcesPath' ) . 'fonts/' . $name ) ? $this->getOption( 'resourcesPath' ) . 'fonts/' . $name : null;
	}

	private function setOption( $name, $value ): void {
		$this->options[ $name ] = $value;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed|null
	 */
	private function getOption( string $name ) {
		return $this->options[ $name ] ?? null;
	}

	/**
	 * @return bool
	 */
	private function validateConfiguration(): bool {
		return $this->options
		       && isset( $this->options['width'], $this->options['height'], $this->options['strokeWidth'], $this->options['minSide'], $this->options['strokeColor'], $this->options['fillColor'], $this->options['outputImageExt'] )
		       && $this->options['width'] > 0
		       && $this->options['height'] > 0
		       && $this->options['minSide'] > 0
		       && $this->options['strokeColor'];
	}

	/**
	 * @return string
	 */
	private function getFilename(): string {
		return md5( serialize( $this->options ) );
	}

	/**
	 * @return bool
	 * @throws ImagickException
	 */
	private function writeCacheImage(): bool {
		// Draw main image
		$imageWidth  = $this->getOption( 'width' );
		$imageHeight = $this->getOption( 'height' );
		$image       = new \Imagick();
		$image->newImage( $imageWidth, $imageHeight, $this->getOption( 'fillColor' ) );
		$image->setImageFormat( $this->getOption( 'outputImageExt' ) );

		// Draw borders on the main image file
		$drawStroke  = new \ImagickDraw();
		$strokeColor = new \ImagickPixel( $this->getOption( 'strokeColor' ) );
		$fillColor   = new \ImagickPixel( $this->getOption( 'fillColor' ) );
		$strokeWidth = $this->getOption( 'strokeWidth' );

		if ( $strokeWidth && $strokeWidth > 0 ) {
			$drawStroke->setStrokeColor( $strokeColor );
			$drawStroke->setFillColor( $fillColor );
			$drawStroke->setStrokeOpacity( 0.3 );
			$drawStroke->setStrokeWidth( $strokeWidth );
			$drawStroke->rectangle( 0, 0, $this->getOption( 'width' ), $this->getOption( 'height' ) );
			$image->drawImage( $drawStroke );
		}

		// Draw icon on the main image file with the given coordinates
		if ( $this->getOption( 'iconFile' ) ) {
			$icon = new \Imagick();

			$icon->setBackgroundColor( new \ImagickPixel( 'transparent' ) );

			// Set bigger resolution for SVG file before rasterize image - for better quality effects
			$icon->setResolution( 5000, 5000 );
			$icon->readImage( $this->getOption( 'iconFile' ) );
			$iconWidth  = $icon->getImageWidth();
			$iconHeight = $icon->getImageHeight();

			// Resize the icon
			$icon->scaleImage( $imageWidth * 0.5, $imageHeight * 0.5, true );

			if ( $imageHeight < $iconHeight || $imageWidth < $iconWidth ) {
				// Get new size
				$iconWidth  = $icon->getImageWidth();
				$iconHeight = $icon->getImageHeight();
			}

			// Calculate the position
			$x = ( $imageWidth - $iconWidth ) / 2;
			$y = ( $imageHeight - $iconHeight ) / 2;

			$icon->setImageFormat( "png24" );

			$image->compositeImage( $icon, Imagick::COMPOSITE_OVER, $x, $y );
		}

		// Draw text
		if ( $this->getOption( 'text' ) && $this->getOption( 'iconFile' ) ) {
			$drawText = new \ImagickDraw();
			$drawText->setFillColor( new \ImagickPixel( '#FFFFFF65' ) );
			/* Font properties */
			$drawText->setFont( $this->getOption( 'fontFile' ) );
			$drawText->setFontSize( 30 );
			$drawText->setGravity( Imagick::GRAVITY_CENTER );
			$textMargin = 20;
			/* Create text */
			$image->annotateImage( $drawText, 0, ( ( $imageHeight - $strokeWidth ) / 2 - $textMargin ), 0, $this->getOption( 'text' ) );
		}

		if ( $ctype = $this->getContentType() ) {
			header( "Content-Type: $ctype" );
		}

		echo $image->getImageBlob();

		return $image->writeImage( $this->getOption( 'cachePath' ) . $this->filename . '.' . $this->getOption( 'outputImageExt' ) ); // fails with no error message
	}

	/**
	 * @throws ImagickException
	 */
	private function getCacheImage(): void {
		$filePath = $this->getOption( 'cachePath' ) . $this->filename . '.' . $this->getOption( 'outputImageExt' );
		if ( file_exists( $filePath ) ) {
			if ( $this->overwriteCache ) {
				$this->writeCacheImage();
			} elseif ( $ctype = $this->getContentType() ) {
				$fileData = file_get_contents( $filePath );
				header( "Content-Type: $ctype" );
				echo $fileData;
			}
		} else {
			$this->writeCacheImage();
		}
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	private static function validateString( string $str ): string {
		return filter_var( str_replace( [ "+" ], ' ', $str ), FILTER_SANITIZE_STRING );
	}

	/**
	 * @return string
	 */
	public static function getServerUrl( $showUri = false ): string {
		$protocol = strpos( strtolower( $_SERVER['SERVER_PROTOCOL'] ), 'https' ) === false ? 'http' : 'https';
		$host     = $_SERVER['HTTP_HOST'];
		$port     = isset( $_SERVER['SERVER_PORT'] ) && (int) $_SERVER['SERVER_PORT'] !== 80 ? ':' . $_SERVER['SERVER_PORT'] : '';
		$uri      = $showUri ? $_SERVER['REQUEST_URI'] : '';

		return $protocol . '://' . $host . $port . $uri;
	}

}

/*
 * For compatibility with older version (rename URL params from old version to current string "colorX" to number "X"])
 * If you don't need this, delete code below
*/
if ( ! empty( $_GET['fillcolor'] ) ) {
	$_GET['fillcolor'] = (int) str_replace( 'color', '', $_GET['fillcolor'] );
}

/*
 * Those args will be used in configuration of generated image
 */
$args = [
	'height'      => $_GET['height'] ?? null,
	'width'       => $_GET['width'] ?? null,
	'fillColor'   => $_GET['fillcolor'] ?? null,
	'strokeWidth' => $_GET['strokewidth'] ?? null,
	'strokeColor' => $_GET['strokecolor'] ?? null,
	'icon'        => $_GET['icon'] ?? null,
	'text'        => $_GET['text'] ?? null,
	'font'        => $_GET['font'] ?? null,
];

/*
 * Init BlankImage class
 */
$image = new BlankImage();

/*
 * Uncomment if you want delete all image files from cache directory
 */
// $image->clearCache();

/*
 * Uncomment if you want overwrite cached file.
 */
// $image->overwriteCache();

/*
 * Example: show helper image
 */
if ( ! $_GET || isset( $_GET['test'] ) ) {
	$image->showHelpImage();
	die();
}

/*
 * Set configuration
 */
$image->configure( $args );

/*
 * Display blank image
 */
$image->showImage();
