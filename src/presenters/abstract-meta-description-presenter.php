<?php
/**
 * Abstract presenter class for the meta description.
 *
 * @package Yoast\YoastSEO\Presenters
 */

namespace Yoast\WP\Free\Presenters;

use Yoast\WP\Free\Models\Indexable;

abstract class Abstract_Meta_Description_Presenter implements Presenter_Interface {
	/**
	 * Returns the meta description for a post.
	 *
	 * @param Indexable $indexable The indexable.
	 *
	 * @return string The meta description tag.
	 */
	public function present( Indexable $indexable ) {
		$meta_description = $this->filter( $this->generate( $indexable ) );

		if ( is_string( $meta_description ) && $meta_description !== '' ) {
			return '<meta name="description" content="' . \esc_attr( \wp_strip_all_tags( \stripslashes( $meta_description ) ) ) . '"/>' . "\n";
		}

		if ( \current_user_can( 'wpseo_manage_options' ) ) {
			return '<!-- ' .
				sprintf(
				/* Translators: %1$s resolves to the SEO menu item, %2$s resolves to the Search Appearance submenu item. */
					\esc_html__( 'Admin only notice: this page does not show a meta description because it does not have one, either write it for this page specifically or go into the [%1$s - %2$s] menu and set up a template.', 'wordpress-seo' ),
					\esc_html__( 'SEO', 'wordpress-seo' ),
					\esc_html__( 'Search Appearance', 'wordpress-seo' )
				) .
				 ' -->' . "\n";
		}

		return '';
	}

	/**
	 * Run the meta description content through the `wpseo_metadesc` filter.
	 *
	 * @param string $meta_description The meta description to filter.
	 *
	 * @return string $meta_description The filtered meta description.
	 */
	private function filter( $meta_description ) {
		/**
		 * Filter: 'wpseo_metadesc' - Allow changing the Yoast SEO meta description sentence.
		 *
		 * @api string $meta_description The description sentence.
		 */
		return (string) trim( \apply_filters( 'wpseo_metadesc', $meta_description ) );
	}

	/**
	 * Generates the meta description for an indexable.
	 *
	 * @param Indexable $indexable The indexable.
	 *
	 * @return string The meta description.
	 */
	protected abstract function generate( Indexable $indexable );
}
