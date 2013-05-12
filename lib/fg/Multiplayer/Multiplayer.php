<?php

/**
 *	@author Félix Girault <felix.girault@gmail.com>
 *	@license FreeBSD License (http://opensource.org/licenses/BSD-2-Clause)
 */

namespace fg\Multiplayer;



/**
 *
 */

class Multiplayer {

	/**
	 *
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
	 *
	 */

	protected $_providers = array(
		'dailymotion' => array(
			'id' => '#dailymotion\\.com/(embed/)?video/(?<id>[a-z0-9]+)#i',
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
			'id' => '#vimeo\\.com/(video/)?(?<id>[0-9]+)#i',
			'player' => 'http://player.vimeo.com/video/%s',
			'map' => array(
				'autoPlay' => 'autoplay',
				'showInfos' => array( 'byline', 'portrait' ),
				'highlightColor' => 'color'
			)
		),
		'youtube' => array(
			'id' => '#(v=|v/|embed/|youtu\\.be/)(?<id>[a-z0-9_-]+)#i',
			'player' => 'http://www.youtube-nocookie.com/embed/%s',
			'map' => array(
				'autoPlay' => 'autoplay',
				'showInfos' => 'showinfo',
				'showRelated' => 'rel'
			)
		)
	);



	/**
	 *
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
		$html = '';

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

			$html .= sprintf( $options['wrapper'], $url );
		} else {

		}

		return $html;
	}



	/**
	 *
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
	 *
	 */

	protected function _params( $provider, $options ) {

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
