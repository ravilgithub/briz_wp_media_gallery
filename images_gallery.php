<?php
namespace Briz_Images_gallery;
use Briz_Shortcodes\common\Helper;

/**
 * 
 * */
class Images_gallery {
	protected $id_prefix = 'briz_images_gallery';


	/**
	 * 
	 * */
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_custom_box' ] );
		add_action( 'save_post', [ $this, 'meta_box_save' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'add_assets' ] );
		add_filter( 'script_loader_tag', [ $this, 'set_module_attr' ], 10, 3 );
	}


	/**
	 * Add CSS and JS.
	 *
	 * Добавление стилей и скриптов.
	 *
	 * @return void.
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function add_assets() {
		$assets = [
			'css' => [
				/************ CSS ************/
				[
					'id'   => $this->id_prefix,
					'src'  => PLUGIN_URL . 'assets/css/' . $this->id_prefix . '.min.css',
					'deps' => [],
					'ver'  => '1.0.0'
				]
			],
			'js' => [
				/************ SCRIPTS ************/
				[
					'id'   => $this->id_prefix,
					'src'  => PLUGIN_URL . 'assets/js/' . $this->id_prefix . '.js',
					'deps' => [ 'jquery' ],
					'ver'  => '1.0.0',
					'in_footer' => true
				]
			]
		];

		$assets = apply_filters( "{$this->id_prefix}_assets", $assets );
		$this->join_assets( $assets, false );
	}


	/**
	 * Registration or enqueue of styles and scripts.
	 *
	 * Регистрация или подключение стилей и скриптов.
	 *
	 * @param Array $assets {
	 *  @type Array $css {
	 *   @type String $id   - id стилей.
	 *   @type String $src  - URL файла стилей.
	 *   @type Array $deps  - другие стили от которых зависит.
	 *                        правильная работа текущего файла стилей.
	 *   @type String $ver  - версия регистрируемого файла стилей( не обязательно ).
	 *  }
	 *  @type Array $js {
	 *   @type String $id   - id скрипта.
	 *   @type String $src  - URL файла.
	 *   @type Array $deps  - другие скрипты от которых зависит.
	 *                        правильная работа текущего скрипта.
	 *   @type String $ver  - версия регистрируемого скрипта( не обязательно ).
	 *   @type Boolean $in_footer - где выводить скрипт: в head или footer.
	 *  }
	 * }
	 *
	 * @param String $register - определяет какое действие совершать,
	 *                           регистрировать или подключать 'CSS' и 'JS'.
	 *                           Available: 'true' or 'false'
	 *                           Default: 'true'
	 *
	 * @see Briz_Shortcodes::merge_shortcode_assets()
	 *  @link ~/main_class.php
	 *
	 * @see Briz_Tax_Shortcode::join_assets()
	 *  @link ~/inc/shortcode_briz_tax.php
	 *
	 * @return void.
	 *
	 * @since 0.0.1
	 * @author Ravil.
	 */
	public function join_assets( $assets, $register = true ) {
		foreach ( $assets as $type => $data ) {
			foreach ( $data as $item ) {
				extract( $item );

				if ( 'css' == $type ) {
					if ( $register ) {
						if ( ! wp_style_is( $id, 'registered' ) )
							wp_register_style( $id, $src, $deps, $ver );
					} else {
						if ( ! wp_style_is( $id, 'enqueued' ) )
							wp_enqueue_style( $id, $src, $deps, $ver );
					}
				} else {
					if ( $register ) {
						if ( ! wp_script_is( $id, 'registered' ) )
							wp_register_script( $id, $src, $deps, $ver, $in_footer );
					} else {
						if ( ! wp_script_is( $id, 'enqueued' ) )
							wp_enqueue_script( $id, $src, $deps, $ver, $in_footer );
					}
				}
			}
		}
	}


	/**
	 * We indicate that the script is a module and, accordingly
	 * will be able to import
	 * functionality from other modules.
	 *
	 * Указываем, что скрипт - это модуль и соответственно
	 * будет иметь возможность импортировать
	 * функционал из других модулей.
	 *
	 * @param String $tag    - HTML код тега <script>.
	 * @param String $handle - Название скрипта (рабочее название),
	 *                         указываемое первым параметром в
	 *                         функции wp_enqueue_script().
	 * @param String $src    - Ссылка на скрипт.
	 *
	 * @return String $tag   - HTML код тега <script>.
	 *
	 * @since 0.0.1
	 * @author Ravil
	 * */
	public function set_module_attr( $tag, $handle, $src ) {
		$module_handle = $this->id_prefix;
		if ( $module_handle === $handle )
			$tag = '<script type="module" src="' . $src . '" id="' . $module_handle . '-js"></script>';
		return $tag;
	}


	/**
	 * 
	 * */
	public function add_custom_box() {
		add_meta_box( $this->id_prefix, 'images gallery', [ $this, 'meta_box_callback' ], [ 'post', 'page' ], 'side', 'high', null );
	}


	/**
	 * Saving, changing or deleting the values of the meta fields of the posts.
	 *
	 * Сохранение, изменение или удаление значений мета полей записи.
	 *
	 * @param String $post_id - ID записи, которая обновляется.
	 * @param Object $post - Объект записи: объект WP_Post.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function meta_box_save( $post_id, $post ) {
		// Helper::debug( $_POST );
		/*if ( ! isset( $_POST[ $this->id_prefix ] ) )
			return;

		foreach ( $_POST[ $this->id_prefix ] as $key => $val ) {
			if ( ! $val && $val !== '0' ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $val );
			}
		}*/

		if ( ! isset( $_POST[ '_' . $this->id_prefix ] ) )
			return;

		$val = $_POST[ '_' . $this->id_prefix ];

		if ( ! $val && $val !== '0' ) {
			delete_post_meta( $post_id, '_' . $this->id_prefix );
		} else {
			update_post_meta( $post_id, '_' . $this->id_prefix, $val );
		}
	}


	/**
	 * 
	 * */
	public function meta_box_callback( $post ) {
		// Helper::debug( $post );
		$defaults = [
			'title'    => 'Insert a media',
			'library'  => [ 'type' => 'image' ],
			/*'library': {
				'type': [ 'video', 'image' ]
			},*/
			'multiple' => true,
			'button'   => [ 'text' => 'Insert' ]
		];

		/*$opts = wp_parse_args( $params[ 'options' ], $defaults );
		extract( $opts );*/

		extract( $defaults );
		$value = get_post_meta( $post->ID, '_' . $this->id_prefix, true );
		// Helper::debug( $value );

		$stage = 'addidable';
		$add_action_txt = __( 'Add медиафайлы' );
		$edit_action_txt = __( 'Edit медиафайлы' );
		$btn_action_txt = $add_action_txt;
		$delBtnClass = '';

		if ( $value && '[]' !== $value ) {
			$stage = 'editable';
			$btn_action_txt = $edit_action_txt;
			$delBtnClass = 'briz-images-gallery-del-media-btn-active';
		}
?>
		<div class="briz-images-gallery-box">
			<div class="briz-images-gallery-add-controls">
				<button
					type="button"
					class="button briz-images-gallery-add-media-btn"
					data-title="<?php echo esc_attr( $title ); ?>"
					data-library-type="<?php echo esc_attr( $library[ 'type' ] ); ?>"
					data-multiple="<?php echo esc_attr( $multiple ); ?>"
					data-button-text="<?php echo esc_attr( $button[ 'text' ] ); ?>"
					data-action-text="<?php echo esc_attr( $edit_action_txt ); ?>"
					data-stage="<?php echo esc_attr( $stage ); ?>"
				>
					<?php echo $btn_action_txt; ?>
				</button>

				<button
					type="button"
					class="button briz-images-gallery-del-media-btn <?php echo esc_attr( $delBtnClass ); ?>"
					data-action-text="<?php echo esc_attr( $add_action_txt ); ?>"
				>
					<?php echo __( 'Удалить медиафайлы' ); ?>
				</button>
			</div>

			<figure>
				<span class="briz-images-gallery-media-place">
<?php

					if ( $value && '[]' !== $value ) :
						$v = json_decode( $value );
						if ( ! empty( $v ) ) :
							foreach ( $v as $media_id ) :
?>
								<span class="briz-images-gallery-media-place-item">
<?php
									$details = wp_prepare_attachment_for_js( $media_id );
									$src = $details[ 'url' ];

									if ( isset( $details[ 'sizes' ][ 'thumbnail' ] ) ) {
										$src = $details[ 'sizes' ][ 'thumbnail' ][ 'url' ];
									}

									// Image
									if ( 'image' == $library[ 'type' ] ) :
?>
										<img
											src="<?php echo esc_attr( $src ); ?>"
											alt="<?php echo esc_attr( $details[ 'alt' ] ); ?>"
										/>
<?php
									// Audio
									elseif ( 'audio' == $library[ 'type' ] ) :
?>
										<audio src="<?php echo esc_attr( $src ); ?>" controls></audio>
<?php
									// Video
									elseif ( 'video' == $library[ 'type' ] ) :
?>
										<video src="<?php echo esc_attr( $src ); ?>" controls></video>
<?php
									endif;

									if ( $caption = $details[ 'caption' ] ) :
?>
										<figcaption>
											<?php echo $caption; ?>
										</figcaption>
<?php
									endif;
?>
								</span> <!-- .briz-images-gallery-media-place-item -->
<?php
							endforeach;
						endif;
					endif;
?>
				</span> <!-- .briz-images-gallery-media-place -->
			</figure>

			<input
				type="hidden"
				name="_<?php echo esc_attr( $this->id_prefix ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
			/>
		</div>
<?php   
	}
}
