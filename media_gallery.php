<?php
namespace Briz_Media_gallery;

/**
 * The class implements the ability to create a gallery from media files.
 *
 * Класс реализует возможность создавать галерею из медиа файлов.
 *
 * @property String lang_domain  - регистрационное "id" файла
 *                                 переводов для всех шорткодов.
 * @property String $id_prefix   - префикс id, JS и CSS файлов.
 * @property Array $screens      - типы записей к которым допустимо добавлять метаблок.
 * @property Array $media_params - параметры мета поля по умолчанию.
 *
 * @since 0.0.1
 * @author Ravil
 */
class Media_gallery {
	protected $lang_domain = 'briz_media_gallery_l10n';
	protected $id_prefix = 'briz_media_gallery';
	protected $screens = [ 'post', 'page' ];
	protected $media_params = [];


	/**
	 * Constructor.
	 *
	 * @param Array $media_params - произвольные параметры мета поля.
	 *
	 * @return void.
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function __construct( $media_params ) {
		$this->set_wp_media_api_params( $media_params );

		add_action( 'add_meta_boxes', [ $this, 'add_custom_box' ] );
		add_action( 'save_post', [ $this, 'meta_box_save' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'join_assets' ] );
		add_filter( 'script_loader_tag', [ $this, 'set_module_attr' ], 10, 3 );
		add_action( 'init', array ( $this, 'set_lang_domain' ) );
	}


	/**
	 * Parameters for WP Media API.
	 *
	 * Параметры для WP Media API.
	 *
	 * @param Array $media_params - произвольные параметры мета поля.
	 *
	 * @return void.
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function set_wp_media_api_params( $media_params ) {
		$params = [
			'title'    => 'Add a media',
			'library'  => [ 'type' => 'image' ],
			/*'library': {
				'type': [ 'video', 'image' ]
			},*/
			'multiple' => true,
			'button'   => [ 'text' => 'Insert' ]
		];

		if ( ! empty( $media_params ) ) {
			$params = wp_parse_args( $media_params, $params );
		}

		$this->media_params = apply_filters( $this->id_prefix . '_wp_media_api_params', $params );
	}


	/**
	 * Register translation file.
	 *
	 * Регистрация файла перевода.
	 *
	 * @return void
	 * @since  0.0.1
	 * @author Ravil
	 */
	public function set_lang_domain() {
		if ( ! is_textdomain_loaded( $this->lang_domain ) ) {
			$default_path = dirname( __FILE__ ) . '/lang/' . $this->lang_domain . '-ru_RU.mo';
			$lang_domain_path = apply_filters( $this->id_prefix . '_lang_domain_path', $default_path );
			load_textdomain( $this->lang_domain, $lang_domain_path );
		}
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
	 * Add an additional block that links media files to a post or page.
	 *
	 * Добавляем дополнительный блок, который привязывает медиа файлы к записи или странице.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function add_custom_box() {
		add_meta_box( $this->id_prefix, __( 'Media gallery', $this->lang_domain ), [ $this, 'meta_box_callback' ], $this->screens, 'side', 'low', null );
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
	 * HTML output meta box content.
	 *
	 * Вывод HTML содержание метабокса.
	 *
	 * @param Object $post - Объект записи: объект WP_Post.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 * @author Ravil
	 */
	public function meta_box_callback( $post ) {
		extract( $this->media_params );
		$value = get_post_meta( $post->ID, '_' . $this->id_prefix, true );

		$stage = 'addidable';
		$add_action_txt = __( 'Add', $this->lang_domain );
		$edit_action_txt = __( 'Edit', $this->lang_domain );
		$btn_action_txt = $add_action_txt;
		$delBtnClass = '';

		if ( $value && '[]' !== $value ) {
			$stage = 'editable';
			$btn_action_txt = $edit_action_txt;
			$delBtnClass = 'briz-media-gallery-del-btn-active';
		}
?>
		<div class="briz-media-gallery-box">
			<div class="briz-media-gallery-add-controls">
				<button
					type="button"
					class="button briz-media-gallery-add-btn"
					data-title="<?php echo esc_attr( __( $title, $this->lang_domain ) ); ?>"
					data-library-type="<?php echo esc_attr( json_encode( $library[ 'type' ] ) ); ?>"
					data-multiple="<?php echo esc_attr( $multiple ); ?>"
					data-button-text="<?php echo esc_attr( __( $button[ 'text' ], $this->lang_domain ) ); ?>"
					data-action-text="<?php echo esc_attr( $edit_action_txt ); ?>"
					data-stage="<?php echo esc_attr( $stage ); ?>"
				>
					<?php echo $btn_action_txt; ?>
				</button>

				<button
					type="button"
					class="button briz-media-gallery-del-btn <?php echo esc_attr( $delBtnClass ); ?>"
					data-action-text="<?php echo esc_attr( $add_action_txt ); ?>"
				>
					<?php _e( 'Delete all', $this->lang_domain ); ?>
				</button>
			</div>

			<div class="briz-media-gallery-place">
<?php
				if ( $value && '[]' !== $value ) :
					$v = json_decode( $value );
					if ( ! empty( $v ) ) :
						foreach ( $v as $media_id ) :
							$details = wp_prepare_attachment_for_js( $media_id );
							$src = $details[ 'url' ];
							$type = $details[ 'type' ];

							if ( isset( $details[ 'sizes' ][ 'thumbnail' ] ) ) {
								$src = $details[ 'sizes' ][ 'thumbnail' ][ 'url' ];
							}
?>
							<figure
								class="briz-media-gallery-place-item <?php echo esc_attr( $type ); ?>"
								data-media-id="<?php echo esc_attr( $media_id ); ?>"
							>
								<i class="briz-image-gallery-del-item">×</i>
<?php
								// Image
								if ( 'image' == $type ) :
?>
									<img
										src="<?php echo esc_attr( $src ); ?>"
										alt="<?php echo esc_attr( $details[ 'alt' ] ); ?>"
									/>
<?php
								endif;

								// Audio
								if ( 'audio' == $type ) :
?>
									<audio src="<?php echo esc_attr( $src ); ?>" controls></audio>
<?php
								endif;

								// Video
								if ( 'video' == $type ) :
?>
									<video src="<?php echo esc_attr( $src ); ?>" controls></video>
<?php
								endif;

								// if ( $caption = $details[ 'caption' ] ) :
?>
									<!-- <figcaption> -->
										<?php // echo $caption; ?>
									<!-- </figcaption> -->
<?php
								// endif;
?>
							</figure> <!-- .briz-media-gallery-place-item -->
<?php
						endforeach;
					endif;
				endif;
?>
			</div> <!-- .briz-media-gallery-place -->
<?php
			wp_nonce_field( 'name_of_my_action','name_of_nonce_field' );
?>
			<input
				type="hidden"
				class="briz-media-gallery-collection"
				name="_<?php echo esc_attr( $this->id_prefix ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
			/>
		</div>
<?php   
	}
}
