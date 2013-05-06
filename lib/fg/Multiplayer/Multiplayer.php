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

	protected $_settings = array(
		'autoPlay' => null,
		'showInfos' => null,
		'showBranding' => null,
		'showRelated' => null,
		'backgroundColor' => null,
		'foregroundColor' => null,
		'highlightColor' => null
	);



	/**
	 *
	 */

	protected $_providers = array(
		'dailymotion' => array(
			'id' => '#dailymotion\\.com/(embed/)?video/(?<id>[a-z0-9]+)#i',
			'player' => 'http://www.dailymotion.com/embed/video/%s',
			'settings' => array(
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
			'settings' => array(
				'autoPlay' => 'autoplay',
				'showInfos' => array( 'byline', 'portrait' ),
				'highlightColor' => 'color'
			)
		),
		'youtube' => array(
			'id' => '#(v=|v/|embed/|youtu\\.be/)(?<id>[a-z0-9_-]+)#i',
			'player' => 'http://www.youtube-nocookie.com/embed/%s',
			'settings' => array(
				'autoPlay' => 'autoplay',
				'showInfos' => 'showinfo',
				'showRelated' => 'rel'
			)
		)
	);



	/**
	 *	HTML code for an <iframe> tag.
	 *
	 *	@var string
	 */

	protected $_iFrame = '<iframe src="%s" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';



	/**
	 *	Prepares an HTML embed code.
	 *
	 *	@param string $source URL or HTML code.
	 *	@return string Prepared HTML code.
	 */

	public function html( $source ) {

		$html = '';
		list( $provider, $id ) = $this->_providerInfos( $source );

		if ( $provider ) {
			$url = sprintf( $this->_providers[ $provider ]['player'], $id );
			$params = $this->_params( $provider );

			if ( $params ) {
				$url .= '?' . http_build_query( $params );
			}

			$html .= sprintf( $this->_iFrame, $url );
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

	protected function _params( $provider ) {

		$params = array( );

		foreach ( $this->_providers[ $provider ]['settings'] as $key => $value ) {
			if ( empty( $this->_settings[ $key ])) {
				continue;
			}

			if ( is_array( $value )) {
				$paramName = $value['key'];
				$paramValue = $this->_settings[ $paramName ];

				if ( isset( $value['prefix'])) {
					$paramValue = $value['prefix'] . $paramValue;
				}
			} else {
				$paramName = $value;
				$paramValue = $this->_settings[ $paramName ];
			}

			$params[ $paramName ] = $paramValue;
		}

		return $params;
	}
}
