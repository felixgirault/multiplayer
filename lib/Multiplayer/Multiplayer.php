<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */

namespace Multiplayer;



/**
 *	Builds HTML embed codes for videos.
 */

class Multiplayer {

	/**
	 *	A HTML code to wrap a player URL.
	 *
	 *	@var string
	 */

	const wrapper = '<iframe src="%s" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';



	/**
	 *	A set of generic parameters.
	 *
	 *	### Options
	 *
	 *	- 'autoPlay' boolean Whether or not to start the video when it is loaded.
	 *	- 'showInfos' boolean
	 *	- 'showBranding' boolean
	 *	- 'showRelated' boolean Whether or not to show related videos at the end.
	 *	- 'backgroundColor' string Hex code of the player's background color.
	 *	- 'foregroundColor' string Hex code of the player's foreground color.
	 *	- 'highlightColor' string Hex code of the player's highlight color.
	 *	- 'start' int The number of seconds at which the video must start.
	 *
	 *	@var array
	 */

	protected $_params = array(
		'autoPlay' => null,
		'showInfos' => null,
		'showBranding' => null,
		'showRelated' => null,
		'backgroundColor' => null,
		'foregroundColor' => null,
		'highlightColor' => null,
		'start' => null
	);



	/**
	 *	A set of configurations indexed by provider name.
	 *
	 *	### options
	 *
	 *	- string Name of the provider.
	 *		- 'id' string A regex to find a video id.
	 *		- 'player' string Base url of the player.
	 *		- 'map' array A map of parameters to translate from generic ones
	 *			to provider-specific ones.
	 *
	 *	@var array
	 */

	protected $_providers = array(
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
	);



	/**
	 *	Constructor.
	 *
	 *	@param array $providers A set of providers to be merged with the
	 *		default ones.
	 */

	public function __construct( array $providers = array( )) {

		$this->_providers = array_merge( $this->_providers, $providers );
	}



	/**
	 *	Builds and returns an HTML embed code.
	 *
	 *	@param string $source URL or HTML code.
	 *	@param array $params Player configuration.
	 *	@param string $wrapper HTML code surrounding the player URL.
	 *	@return string Prepared HTML code.
	 */

	public function html( $source, array $params = array( ), $wrapper = self::wrapper ) {

		$params += $this->_params;
		$html = $source;

		list( $provider, $videoId ) = $this->_providerInfos( $source );

		if ( $provider ) {
			$params = $this->_mappedParams(
				$this->_providers[ $provider ]['map'],
				$params
			);

			$url = sprintf(
				$this->_providers[ $provider ]['player'],
				$videoId
			);

			if ( $params ) {
				$url .= '?' . http_build_query( $params );
			}

			$html = sprintf( $wrapper, $url );
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

		foreach ( $this->_providers as $name => $options ) {
			if ( preg_match( $options['id'], $source, $matches )) {
				return array( $name, $matches['id']);
			}
		}

		return array( false, false );
	}



	/**
	 *	Builds and returns an array of mapped parameters.
	 *
	 *	@param array $map A map to translate the parameters.
	 *	@param array $options Generic parameters.
	 */

	protected function _mappedParams( array $map, array $params ) {

		$mapped = array( );

		// translation from generic parameters to specific ones

		foreach ( $map as $generic => $specific ) {
			if ( empty( $params[ $generic ])) {
				continue;
			}

			$value = $params[ $generic ];

			if ( is_array( $specific )) {
				if ( empty( $specific['param'])) {
					continue;
				}

				$param = $specific['param'];

				if ( $value && isset( $specific['prefix'])) {
					$value = $specific['prefix'] . $value;
				}
			} else {
				$param = $specific;
			}

			$mapped[ $param ] = $value;
		}

		// handling of non generic parameters

		$extra = array_diff_key( $params, $this->_params );

		if ( !empty( $extra )) {
			$mapped = array_merge( $mapped, $extra );
		}

		return $mapped;
	}
}
