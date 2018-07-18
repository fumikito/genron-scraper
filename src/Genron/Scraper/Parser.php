<?php

namespace Genron\Scraper;

use Sunra\PhpSimple\HtmlDomParser;

/**
 * Get parser
 *
 * @package genron-scraper
 * @property int $year
 */
class Parser {

	protected $prefix = 'sf';

	protected $_year = false;

	/**
	 * Build URL to scrape
	 *
	 * @param string $slug
	 * @param int    $year
	 *
	 * @return string
	 */
	public function build_url( $slug, $year = 0 ) {
		if ( $year ) {
			$this->_year = $year;
		}
		return sprintf( 'http://school.genron.co.jp/works/sf/%d/subjects/%s/', $this->year, $slug );
	}

	/**
	 * Get all students
	 *
	 * @param string $slug
	 * @param int $year
	 * @return array
	 */
	public function get_students( $slug, $year = 0 ) {
		$url = $this->build_url( $slug, $year );
		$html = HtmlDomParser::file_get_html( $url );
		$students = [];
		foreach ( $html->find('.students li, ') as $li ) {
			$link       = $li->find( 'a', 0 );
			$class_name = (string) $li->class;
			$rank       = preg_match( '#rank-(\d)#u', $class_name, $match ) ? (int) $match[1] : 0;
			$href       = $link ? (string) $link->href : '';
			$selected   = false !== strpos( $class_name, 'type-excellents' );
			$title      = '';
			$character  = 0;
			if ( $link ) {
				$work = HtmlDomParser::file_get_html( $href );
				if ( $work ) {
					$title = trim( $work->find('.summary-title', 0)->plaintext );
					if ( $main_title = $work->find('.work-title', 0) ) {
						$title = trim( $main_title->plaintext );
						$character = preg_replace( '#\D#u', '', $work->find('.work-content .count-character', 0)->plaintext );
					}
				}
			}
			$students[] = [
				'title'     => $title,
				'url'       => $href,
				'work'      => false !== strpos( $class_name, 'has-work' ) ? '1' : '0',
				'character' => $character,
				'selected'  => $selected,
				'rank'      => $rank,
				'name'      => trim( $li->find('.name', 0)->plaintext ),
				'profile'   => $link ? preg_replace( '#\d+/$#u', '', $href ) : false
			];
			sleep( 1 );
		}
		return $students;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'year':
				if ( ! $this->_year ) {
					$this->_year = date( 'Y' );
				}
				return $this->_year;
				break;
			default:
				return null;
				break;
		}
	}
}
