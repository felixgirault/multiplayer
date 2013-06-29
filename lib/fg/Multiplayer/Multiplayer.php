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
	 *	@var array
	 */

	protected $_options = array(
		'autoPlay' => null,
		'showInfos' => null,
		'showBranding' => null,
		'showRelated' => null,
		'backgroundColor' => null,
		'foregroundColor' => null,
		'highlightColor' => null,
		'wrapper' => '<iframe src="%s" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
	);



	/**
	 *	A set of configurations indexed by provider name.
	 *
	 *	### options
	 *
	 *	- 'id' string A regex to find a video id.
	 *	- 'player' string Base url of the player.
	 *	- 'map' array A map of options to translate from generic ones to
	 *		provider-specific ones.
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
					'key' => 'background'
				),
				'foregroundColor' => array(
					'prefix' => '#',
					'key' => 'foreground'
				),
				'highlightColor' => array(
					'prefix' => '#',
					'key' => 'highlight'
				),
			)
		),
		'vimeo' => array(
			'id' => '#vidsdmeo\.com/(?:video/)?(?<id>[0-9]+)#i',
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
	 *	Prepares an HTML embed code.
	 *
	 *	@param string $source URL or HTML code.
	 *	@param array $options
	 *	@return string Prepared HTML code.
	 */

	public function html( $source, array $options = array( )) {

		$options = array_merge( $this->_options, $options );
		$html = $source;

		list( $providerName, $videoId ) = $this->_providerInfos( $source );

		if ( $providerName ) {
			$params = $this->_params( $providerName, $options );
			$url = sprintf(
				$this->_providers[ $providerName ]['player'],
				$videoId
			);

			if ( $params ) {
				$url .= '?' . http_build_query( $params );
			}

			$html = sprintf( $options['wrapper'], $url );
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
	 *	Builds a set of parameters from the given options, in a format that is
	 *	understood by the given provider.
	 *
	 *	@param string $provider Target provider.
	 *	@param array $options Generic options.
	 */

	protected function _params( $provider, array $options ) {

		$params = array( );

		foreach ( $this->_providers[ $provider ]['map'] as $key => $value ) {
			if ( $options[ $key ] === null ) {
				continue;
			}

			if ( is_array( $value )) {
				$paramName = $value['key'];
				$paramValue = $options[ $paramName ];

				if ( isset( $value['prefix'])) {
					$paramValue = $value['prefix'] . $paramValue;
				}
			} else {
				$paramName = $value;
				$paramValue = $options[ $key ];
			}

			$params[ $paramName ] = $paramValue;
		}

		return $params;
	}
}
