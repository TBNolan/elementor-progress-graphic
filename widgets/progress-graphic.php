<?php
namespace ElementorProgressGraphic\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Schemes;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use ElementorPro\Base\Base_Widget;
use ElementorPro\Modules\QueryControl\Module as Module_Query;
use ElementorPro\Modules\QueryControl\Controls\Group_Control_Related;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor Hello World
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class Progress_Graphic extends Widget_Base {

/**
	 * @var \WP_Query
	 */
	private $_query = null;

	protected $_has_template_content = false;

	public function get_name() {
		return 'progress-graphic';
	}

	public function get_title() {
		return __( 'progress-graphic', 'elementor-progress-graphic' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_keywords() {
		return [ 'posts', 'cpt', 'item', 'loop', 'query', 'portfolio', 'custom post type' ];
	}

	public function get_script_depends() {
		return [ 'imagesloaded' ];
	}

	public function on_import( $element ) {
		if ( ! get_post_type_object( $element['settings']['posts_post_type'] ) ) {
			$element['settings']['posts_post_type'] = 'post';
		}

		return $element;
	}

	public function get_query() {
		return $this->_query;
	}

	protected function _register_controls() {
		$this->register_query_section_controls();
	}

	private function register_query_section_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);



		$this->add_control(
			'title_tag',
			[
				'label' => __( 'Title HTML Tag', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
				'default' => 'h3',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_group_control(
			Group_Control_Related::get_type(),
			[
				'name' => 'posts',
				'presets' => [ 'full' ],
				'exclude' => [
					'posts_per_page', //use the one from Layout section
					'orderby', //use ACF field to order posts
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_design_overlay',
			[
				'label' => __( 'Item Overlay', 'elementor-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'unseen_color_title',
			[
				'label' => __( 'Color of Unseen Movies', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a .elementor-portfolio-item__overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'seen_color_title',
			[
				'label' => __( 'Color of Seen Movies', 'elementor-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a .elementor-portfolio-item__overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography_title',
				'scheme' => Schemes\Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .elementor-portfolio-item__title',
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_posts_tags() {
		$taxonomy = $this->get_settings( 'taxonomy' );

		foreach ( $this->_query->posts as $post ) {
			if ( ! $taxonomy ) {
				$post->tags = [];

				continue;
			}

			$tags = wp_get_post_terms( $post->ID, $taxonomy );

			$tags_slugs = [];

			foreach ( $tags as $tag ) {
				$tags_slugs[ $tag->term_id ] = $tag;
			}

			$post->tags = $tags_slugs;
		}
	}

	public function query_posts() {

		$query_args = [
			'posts_per_page' => -1,
		];

		/** @var Module_Query $elementor_query */
		$elementor_query = Module_Query::instance();
		$this->_query = $elementor_query->get_query( $this, 'posts', $query_args, [] );
	}

	public function get_movie_array() {
		$movieArray = [];
		$movieMetaKey = 'movie-statuses';
		if (is_user_logged_in()) {
            $movieArray = (get_user_meta(get_current_user_id(), $movieMetaKey, true)) ? get_user_meta(get_current_user_id(), $movieMetaKey, true) : "" ;
		}
		return $movieArray;
	}
	public function render_css() {
		$settings = $this->get_settings_for_display();
		$html = "";
		$html .= '<style type="text/css" scoped>';
		$html .= '.movie-title.seen { color: '. $settings['seen_color_title'] .'; text-decoration: line-through; opacity: 0.6; }';
		$html .= '.movie-title { color: '. $settings['unseen_color_title'] .'; text-decoration: none; }';
		$html .= '.progress-graphic { line-height: 1; letter-spacing: -1px; font-family: "Roboto",sans-serif; font-weight: 400; font-size: 13px;';
		$html .= '</style>';
		echo $html;
	}

	public function render_log_in_button() {
		$html = "";
		$html .= '<div class="please-login">';
		$html .= '<a href="/login/">';
		$html .= '<button class="elementor-button-link elementor-button elementor-size-sm" style="margin-bottom: 15px;">Login to track progress</button>';
		$html .= '</a>';
		$html .= '</div>';
		echo $html;
	}

	public function render() {	
		if(!is_user_logged_in()) {
			$this->render_log_in_button();
		}
		$movieArray = $this->get_movie_array();	
		$this->query_posts();

		$wp_query = $this->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}

		$this->get_posts_tags();

		$this->render_loop_header();
		$this->render_css();

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();

			$this->render_post($movieArray);
		}

		$this->render_loop_footer();

		wp_reset_postdata();
	}

	protected function check_seen_status($movieArray) {
        global $post;
        switch ($movieArray[$post->ID]['seen']) {
            case 1:
                return true;
                break;
            case 0:
            default:
                return false;
                break;
		}
	}

	protected function render_title($movieArray) {
		$seen = $this->check_seen_status($movieArray);
		$tag = $this->get_settings( 'title_tag' );
		echo '<' . $tag . ' class="movie-title' . ($seen ? " seen" : "") .'">';
		the_title();
		echo '</' . $tag .'>';
	}

	protected function render_loop_header() {
		?>
		<div class="progress-graphic">
		<?php
	}

	protected function render_loop_footer() {
		?>
		</div>
		<?php
	}

	protected function render_post($movieArray) {
		$this->render_title($movieArray);
	}

}
