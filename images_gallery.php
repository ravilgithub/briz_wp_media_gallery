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
		add_action( 'admin_enqueue_scripts', [ $this, 'join_assets' ] );
		add_filter( 'script_loader_tag', [ $this, 'set_module_attr' ], 10, 3 );
	}


	/**
	 * Set CSS and JS.
	 *
	 * Добавление стилей и скриптов.
	 *
	 * @var Array $assets {
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
	 * @return void.
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function set_assets() {
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

		return apply_filters( "{$this->id_prefix}_assets", $assets );
	}


	/**
	 * Enqueue of styles and scripts.
	 *
	 * Подключение стилей и скриптов.
	 *
	 * @return void.
	 *
	 * @since 0.0.1
	 * @author Ravil.
	 */
	public function join_assets() {
		$assets = $this->set_assets();

		foreach ( $assets as $type => $data ) {
			foreach ( $data as $item ) {
				extract( $item );

				if ( 'css' == $type ) {
					if ( ! wp_style_is( $id, 'enqueued' ) )
						wp_enqueue_style( $id, $src, $deps, $ver );
				} else {
					if ( ! wp_script_is( $id, 'enqueued' ) )
						wp_enqueue_script( $id, $src, $deps, $ver, $in_footer );
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
		if (
			! isset( $_POST[ 'name_of_nonce_field' ] ) ||
			! wp_verify_nonce( $_POST[ 'name_of_nonce_field' ], 'name_of_my_action' )
		) return;

		if (
			! current_user_can( 'edit_post', $post_id ) ||
			! current_user_can( 'edit_page', $post_id )
		) return;

		if ( ! isset( $_POST[ '_' . $this->id_prefix ] ) )
			return;

		$val = $_POST[ '_' . $this->id_prefix ];

		if ( ! $val && $val !== '0' )
			delete_post_meta( $post_id, '_' . $this->id_prefix );
		else
			update_post_meta( $post_id, '_' . $this->id_prefix, $val );
	}


	/**
	 * 
	 * */
	public function meta_box_callback( $post ) {
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
<?php
			wp_nonce_field( 'name_of_my_action','name_of_nonce_field' );
?>
			<input
				type="hidden"
				class="briz-images-gallery-media-collection"
				name="_<?php echo esc_attr( $this->id_prefix ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
			/>
		</div>
<?php   
	}
}
