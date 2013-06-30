<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */

namespace fg\Multiplayer;



/**
 *	Builds HTML embed codes for videos.
 */

class Multiplayer {

	/**
	 *	Names of the available options.
	 *
	 *	### options
	 *
	 *	- 'wrapper' string A HTML code to wrap the video url.
	 *	- 'params' array A set of generic parameters.
	 *		- 'autoPlay' boolean Whether or not to start the video when it is loaded.
	 *		- 'showInfos' boolean
	 *		- 'showBranding' boolean
	 *		- 'showRelated' boolean Whether or not to show related videos at the end.
	 *		- 'backgroundColor' string Hex code of the player's background color.
	 *		- 'foregroundColor' string Hex code of the player's foreground color.
	 *		- 'highlightColor' string Hex code of the player's highlight color.
	 *	- 'providers' array A set of configurations indexed by provider name.
	 *		- 'id' string A regex to find a video id.
	 *		- 'player' string Base url of the player.
	 *		- 'map' array A map of parameters to translate from generic ones
	 *			to provider-specific ones.
	 *
	 *	@var array
	 */

	protected $_options = array(
		'wrapper' => '<iframe src="%s" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
		'params' => array(
			'autoPlay' => null,
			'showInfos' => null,
			'showBranding' => null,
			'showRelated' => null,
			'backgroundColor' => null,
			'foregroundColor' => null,
			'highlightColor' => null,
		),
		'providers' => array(
			'dailymotion' => array(
				'id' => '#dailymotion\.com/(?:embed/)?video/(?<id>[a-z0-9]+)#i',
				'player' => 'http://www.dailymotion.com/embed/video/%s',
				'map' => array(
					'autoPlay' => 'autoplay',
					'showInfos' => 'info',
					'showBranding' => 'logo',
					'showRelated' => 'related',
					'backgroundColor' => array(
						'prefix' => '#',
						'param' => 'background'
					),
					'foregroundColor' => array(
						'prefix' => '#',
						'param' => 'foreground'
					),
					'highlightColor' => array(
						'prefix' => '#',
						'param' => 'highlight'
					),
				)
			),
			'vimeo' => array(
				'id' => '#vimeo\.com/(?:video/)?(?<id>[0-9]+)#i',
				'player' => 'http://player.vimeo.com/video/%s',
				'map' => array(
					'autoPlay' => 'autoplay',
					'showInfos' => array( 'byline', 'portrait' ),
					'highlightColor' => 'color'
				)
			),
			'youtube' => array(
				'id' => '#(?:v=|v/|embed/|youtu\.be/)(?<id>[a-z0-9_-]+)#i',
				'player' => 'http://www.youtube-nocookie.com/embed/%s',
				'map' => array(
					'autoPlay' => 'autoplay',
					'showInfos' => 'showinfo',
					'showRelated' => 'rel'
				)
			)
		)
	);



	/**
	 *	Constructor.
	 *
	 *	@param array $options A set of options to be merged with the
	 *		default ones.
	 */

	public function __construct( array $options = array( )) {

		$this->_options = array_merge( $this->_options, $options );
	}



	/**
	 *	Prepares an HTML embed code.
	 *
	 *	@param string $source URL or HTML code.
	 *	@param array $params
	 *	@return string Prepared HTML code.
	 */

	public function html( $source, array $params = array( )) {

		$params += $this->_options['params'];
		$html = $source;

		list( $providerName, $videoId ) = $this->_providerInfos( $source );

		if ( $providerName ) {
			$params = $this->_params( $providerName, $params );
			$url = sprintf(
				$this->_options['providers'][ $providerName ]['player'],
				$videoId
			);

			if ( $params ) {
				$url .= '?' . http_build_query( $params );
			}

			$html = sprintf( $this->_options['wrapper'], $url );
		}

		return $html;
	}



	/**
	 *	Finds informations about the provider providing the given source.
	 *
	 *	@param string $source URL or HTML code.
	 *	@return array The name of the provider, and the video id found in the
	 *		source.
	 */

	protected function _providerInfos( $source ) {

		foreach ( $this->_options['providers'] as $name => $options ) {
			if ( preg_match( $options['id'], $source, $matches )) {
				return array( $name, $matches['id']);
			}
		}

		return array( false, false );
	}



	/**
	 *	Builds a set of parameters from the given options, in a format that is
	 *	understood by the given provider.
	 *
	 *	@param string $provider Target provider.
	 *	@param array $options Generic options.
	 */

	protected function _params( $provider, array $options ) {

		$params = array( );

		foreach ( $this->_options['providers'][ $provider ]['map'] as $key => $value ) {
			if ( empty( $options[ $key ])) {
				continue;
			}

			$paramValue = $options[ $key ];

			if ( is_array( $value )) {
				if ( empty( $value['param'])) {
					continue;
				}

				$paramName = $value['param'];

				if ( $paramValue && isset( $value['prefix'])) {
					$paramValue = $value['prefix'] . $paramValue;
				}
			} else {
				$paramName = $value;
			}

			$params[ $paramName ] = $paramValue;
		}

		return $params;
	}
}