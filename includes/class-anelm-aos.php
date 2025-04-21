<?php
/**
 * Animate Elementor AOS Class.
 *
 * Provides Animate On Scroll (AOS) integration for Elementor widgets.
 *
 * @package Animate_Elementor\Includes
 */

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly.
endif;

if ( ! class_exists( 'ANELM_AOS' ) ) :

	/**
	 * Class ANELM_AOS
	 *
	 * Hooks into Elementor and provides AOS animation controls and frontend behavior.
	 */
	class ANELM_AOS {

		/**
		 * Constructor.
		 * Registers required hooks for enqueueing scripts, adding controls, and setting attributes.
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_aos_assets' ) );
			add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'initialize_aos_script' ) );
			add_action( 'elementor/element/after_section_end', array( $this, 'add_aos_controls' ), 1000, 3 );
			add_action( 'elementor/frontend/before_render', array( $this, 'add_aos_attributes' ), 1000, 1 );
		}

		/**
		 * Enqueue AOS CSS and JS from CDN.
		 */
		public function enqueue_aos_assets() {
			wp_enqueue_style(
				'animate-elementor-aos',
				ANELM_URL . 'assets/css/aos.css',
				array(),
				ANELM_VERSION
			);

			wp_enqueue_script(
				'animate-elementor-aos',
				ANELM_URL . 'assets/js/aos.js',
				array(),
				ANELM_VERSION,
				true
			);
		}

		/**
		 * Initialize AOS when DOM is ready.
		 */
		public function initialize_aos_script() {
			wp_add_inline_script(
				'animate-elementor-aos',
				'document.addEventListener("DOMContentLoaded", function() { AOS.init(); });'
			);
		}

		/**
		 * Add AOS animation controls to all Elementor widgets under the Advanced tab.
		 *
		 * @param \Elementor\Element_Base $element     The widget element.
		 * @param string                  $section_id  Section ID.
		 * @param array                   $args        Additional arguments.
		 */
		public function add_aos_controls( $element, $section_id, $args ) {
			if ( '_section_responsive' !== $section_id ) {
				return;
			}

			$element->start_controls_section(
				'animate_elementor_aos_section',
				array(
					'label' => __( 'Animate Elementor', 'animate-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);

			$element->add_control(
				'animate_elementor_aos_enable',
				array(
					'label'        => __( 'Enable On Scroll Animation', 'animate-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'animate-elementor' ),
					'label_off'    => __( 'No', 'animate-elementor' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$element->add_control(
				'animate_elementor_aos_animation',
				array(
					'label'     => __( 'Animation Type', 'animate-elementor' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $this->get_animation_types(),
					'default'   => 'fade-up',
					'condition' => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_offset',
				array(
					'label'     => __( 'Offset (px)', 'animate-elementor' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'default'   => 120,
					'condition' => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_delay',
				array(
					'label'     => __( 'Delay (ms)', 'animate-elementor' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'default'   => 0,
					'condition' => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_duration',
				array(
					'label'     => __( 'Duration (ms)', 'animate-elementor' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'default'   => 400,
					'condition' => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_easing',
				array(
					'label'     => __( 'Easing', 'animate-elementor' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $this->get_easing_options(),
					'default'   => 'ease',
					'condition' => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_once',
				array(
					'label'        => __( 'Animate Only Once?', 'animate-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => '',
					'condition'    => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_mirror',
				array(
					'label'        => __( 'Mirror Animation on Scroll Up?', 'animate-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'true',
					'default'      => '',
					'condition'    => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_anchor_placement',
				array(
					'label'     => __( 'Anchor Placement', 'animate-elementor' ),
					'type'      => \Elementor\Controls_Manager::SELECT,
					'options'   => $this->get_anchor_placements(),
					'default'   => 'top-bottom',
					'condition' => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_disable',
				array(
					'label'       => __( 'Disable AOS on Devices', 'animate-elementor' ),
					'type'        => \Elementor\Controls_Manager::SELECT,
					'default'     => '',
					'options'     => array(
						'' 	      => __( 'none', 'animate-elementor' ),
						'tablet'  => __( 'Tablet', 'animate-elementor' ),
						'mobile'  => __( 'Mobile', 'animate-elementor' ),
					),
					'condition'   => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);
			

			$element->add_control(
				'animate_elementor_aos_start_event',
				array(
					'label'       => __( 'Start Event', 'animate-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => 'DOMContentLoaded',
					'description' => __( 'Event that triggers AOS initialization. default: DOMContentLoaded', 'animate-elementor' ),
					'condition'   => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_init_class',
				array(
					'label'       => __( 'Init Class Name', 'animate-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => 'aos-init',
					'description' => __( 'Class applied after initialization.', 'animate-elementor' ),
					'condition'   => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_animated_class',
				array(
					'label'       => __( 'Animated Class Name', 'animate-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => 'aos-animate',
					'description' => __( 'Class applied when animation is triggered.', 'animate-elementor' ),
					'condition'   => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->add_control(
				'animate_elementor_aos_use_classnames',
				array(
					'label'        => __( 'Use Class Names?', 'animate-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'description'  => __( 'Apply `data-aos` value as a class name.', 'animate-elementor' ),
					'return_value' => 'true',
					'default'      => '',
					'condition'    => array( 'animate_elementor_aos_enable' => 'yes' ),
				)
			);

			$element->end_controls_section();
		}

		/**
		 * Add AOS attributes to Elementor element frontend wrapper.
		 *
		 * @param \Elementor\Element_Base $element The widget element.
		 */
		public function add_aos_attributes( $element ) {
			$settings = $element->get_settings_for_display();

			if ( empty( $settings['animate_elementor_aos_enable'] ) || 'yes' !== $settings['animate_elementor_aos_enable'] ) :
				return;
			endif;

			$element->add_render_attribute( '_wrapper', 'data-aos', $settings['animate_elementor_aos_animation'] );
			$element->add_render_attribute( '_wrapper', 'data-aos-offset', $settings['animate_elementor_aos_offset'] );
			$element->add_render_attribute( '_wrapper', 'data-aos-delay', $settings['animate_elementor_aos_delay'] );
			$element->add_render_attribute( '_wrapper', 'data-aos-duration', $settings['animate_elementor_aos_duration'] );
			$element->add_render_attribute( '_wrapper', 'data-aos-easing', $settings['animate_elementor_aos_easing'] );

			if ( ! empty( $settings['animate_elementor_aos_once'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-once', 'true' );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_mirror'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-mirror', 'true' );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_anchor_placement'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-anchor-placement', $settings['animate_elementor_aos_anchor_placement'] );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_disable'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-disable', 'true' );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_start_event'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-start-event', esc_attr( $settings['animate_elementor_aos_start_event'] ) );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_init_class'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-init-class-name', esc_attr( $settings['animate_elementor_aos_init_class'] ) );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_animated_class'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-animated-class-name', esc_attr( $settings['animate_elementor_aos_animated_class'] ) );
			endif;

			if ( ! empty( $settings['animate_elementor_aos_use_classnames'] ) ) :
				$element->add_render_attribute( '_wrapper', 'data-aos-use-classnames', 'true' );
			endif;
		}

		/**
		 * Returns available AOS animation types.
		 *
		 * @return array List of AOS animation types.
		 */
		private function get_animation_types() {
			return array(
				'fade'            => esc_html__( 'Fade', 'animate-elementor' ),
				'fade-up'         => esc_html__( 'Fade Up', 'animate-elementor' ),
				'fade-down'       => esc_html__( 'Fade Down', 'animate-elementor' ),
				'fade-left'       => esc_html__( 'Fade Left', 'animate-elementor' ),
				'fade-right'      => esc_html__( 'Fade Right', 'animate-elementor' ),
				'fade-up-right'   => esc_html__( 'Fade Up Right', 'animate-elementor' ),
				'fade-up-left'    => esc_html__( 'Fade Up Left', 'animate-elementor' ),
				'fade-down-right' => esc_html__( 'Fade Down Right', 'animate-elementor' ),
				'fade-down-left'  => esc_html__( 'Fade Down Left', 'animate-elementor' ),
				'flip-left'       => esc_html__( 'Flip Left', 'animate-elementor' ),
				'flip-right'      => esc_html__( 'Flip Right', 'animate-elementor' ),
				'flip-up'         => esc_html__( 'Flip Up', 'animate-elementor' ),
				'flip-down'       => esc_html__( 'Flip Down', 'animate-elementor' ),
				'slide-up'        => esc_html__( 'Slide Up', 'animate-elementor' ),
				'slide-down'      => esc_html__( 'Slide Down', 'animate-elementor' ),
				'slide-left'      => esc_html__( 'Slide Left', 'animate-elementor' ),
				'slide-right'     => esc_html__( 'Slide Right', 'animate-elementor' ),
				'zoom-in'         => esc_html__( 'Zoom In', 'animate-elementor' ),
				'zoom-in-up'      => esc_html__( 'Zoom In Up', 'animate-elementor' ),
				'zoom-in-down'    => esc_html__( 'Zoom In Down', 'animate-elementor' ),
				'zoom-in-left'    => esc_html__( 'Zoom In Left', 'animate-elementor' ),
				'zoom-in-right'   => esc_html__( 'Zoom In Right', 'animate-elementor' ),
				'zoom-out'        => esc_html__( 'Zoom Out', 'animate-elementor' ),
				'zoom-out-up'     => esc_html__( 'Zoom Out Up', 'animate-elementor' ),
				'zoom-out-down'   => esc_html__( 'Zoom Out Down', 'animate-elementor' ),
				'zoom-out-left'   => esc_html__( 'Zoom Out Left', 'animate-elementor' ),
				'zoom-out-right'  => esc_html__( 'Zoom Out Right', 'animate-elementor' ),
			);
		}

		/**
		 * Returns available easing options.
		 *
		 * @return array List of easing types.
		 */
		private function get_easing_options() {
			return array(
				'linear'            => esc_html__( 'Linear', 'animate-elementor' ),
				'ease'              => esc_html__( 'Ease', 'animate-elementor' ),
				'ease-in'           => esc_html__( 'Ease In', 'animate-elementor' ),
				'ease-out'          => esc_html__( 'Ease Out', 'animate-elementor' ),
				'ease-in-out'       => esc_html__( 'Ease In Out', 'animate-elementor' ),
				'ease-in-back'      => esc_html__( 'Ease In Back', 'animate-elementor' ),
				'ease-out-back'     => esc_html__( 'Ease Out Back', 'animate-elementor' ),
				'ease-in-out-back'  => esc_html__( 'Ease In Out Back', 'animate-elementor' ),
				'ease-in-sine'      => esc_html__( 'Ease In Sine', 'animate-elementor' ),
				'ease-out-sine'     => esc_html__( 'Ease Out Sine', 'animate-elementor' ),
				'ease-in-out-sine'  => esc_html__( 'Ease In Out Sine', 'animate-elementor' ),
				'ease-in-quad'      => esc_html__( 'Ease In Quad', 'animate-elementor' ),
				'ease-out-quad'     => esc_html__( 'Ease Out Quad', 'animate-elementor' ),
				'ease-in-out-quad'  => esc_html__( 'Ease In Out Quad', 'animate-elementor' ),
				'ease-in-cubic'     => esc_html__( 'Ease In Cubic', 'animate-elementor' ),
				'ease-out-cubic'    => esc_html__( 'Ease Out Cubic', 'animate-elementor' ),
				'ease-in-out-cubic' => esc_html__( 'Ease In Out Cubic', 'animate-elementor' ),
				'ease-in-quart'     => esc_html__( 'Ease In Quart', 'animate-elementor' ),
				'ease-out-quart'    => esc_html__( 'Ease Out Quart', 'animate-elementor' ),
				'ease-in-out-quart' => esc_html__( 'Ease In Out Quart', 'animate-elementor' ),
			);
		}

		/**
		 * Returns anchor placement options.
		 *
		 * @return array List of anchor placement values.
		 */
		private function get_anchor_placements() {
			return array(
				'top-bottom'    => esc_html__( 'Top Bottom', 'animate-elementor' ),
				'top-center'    => esc_html__( 'Top Center', 'animate-elementor' ),
				'top-top'       => esc_html__( 'Top Top', 'animate-elementor' ),
				'center-bottom' => esc_html__( 'Center Bottom', 'animate-elementor' ),
				'center-center' => esc_html__( 'Center Center', 'animate-elementor' ),
				'center-top'    => esc_html__( 'Center Top', 'animate-elementor' ),
				'bottom-bottom' => esc_html__( 'Bottom Bottom', 'animate-elementor' ),
				'bottom-center' => esc_html__( 'Bottom Center', 'animate-elementor' ),
				'bottom-top'    => esc_html__( 'Bottom Top', 'animate-elementor' ),
			);
		}
	}

endif;
