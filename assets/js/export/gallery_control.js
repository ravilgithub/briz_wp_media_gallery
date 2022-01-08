/**
 * Функционал описывающий работу с медиа файлами.
 * Добавление, изменение и удаление медиа файлов в мета полях.
 *
 * @property Object selectors - селекторы HTML элементов мета поля.
 *
 * @since 0.0.1
 * @autor Ravil
 */
export default {
	selectors: {
		btn: {
			add: '.briz-images-gallery-add-media-btn',
			del: '.briz-images-gallery-del-media-btn'
		},
		mediaPlace: '.briz-images-gallery-media-place',
		input: '.briz-images-gallery-media-collection',
		activeClass: 'briz-images-gallery-del-media-btn-active',
	},


	/**
	 * Создаём объект wp.media и
	 * передаём ему первоначальные данные.
	 *
	 * @param DOM Object instance - родительский элемент мета поля "media button".
	 *
	 * @return {void}
	 */
	add( instance ) {
		instance.querySelector( this.selectors.btn.add ).addEventListener( 'click', evt => {
			const btn = evt.target,
			      args = {
			        title: btn.dataset.title,
			        library: { type: JSON.parse( btn.dataset.libraryType ) },
			        multiple: btn.dataset.multiple,
			        button: { text: btn.dataset.buttonText }
			      },
			      wpm = wp.media( args );

			this.select( wpm, instance );
			this.open( wpm, instance );
			wpm.open();
		}, false );
	},


	/**
	 * Формирование HTML выбранных медиа файлов.
	 *
	 * @param Object wpm - WP Media Object.
	 * @param Object atts - свойства выбранного медиа файла.
	 *
	 * @return String html - разметка медиа файлa и подпись к нему если имеется.
	 */
	createEl( wpm, atts ) {
		const tagName = atts.type;
		const item = document.createElement( 'span' );
		item.classList.add( 'briz-media-place-item' );

		let attrs = { src: atts.url }, // для 'audio' и 'video'.
		    html = '',
		    media = '';

		if ( 'image' == tagName ) {
			attrs[ 'alt' ] = atts.alt;
			attrs[ 'src' ] = atts.sizes.thumbnail.url || atts.sizes.full.url;
		} else if ( 'audio' == tagName ) {
			attrs[ 'controls' ] = 'controls';
		} else if ( 'video' == tagName ) {
			attrs[ 'controls' ] = 'controls';
		}

		media = document.createElement( tagName );
		for ( let i in attrs ) {
			media.setAttribute( i, attrs[ i ] );
		}

		item.appendChild( media );

		if ( atts.caption ) {
			const figcaption = document.createElement( 'figcaption' );
			figcaption.textContent = atts.caption;
			item.appendChild( figcaption );
		}

		return item.outerHTML;
	},


	/**
	 * Обработчик выбора медиа данных в библиотеке.
	 * Формирование данных о выбранных медиа файлах.
	 *
	 * @param Object wpm - WP Media Object.
	 * @param DOM Object instance - родительский элемент мета поля "media button".
	 *
	 * @return {void}
	 */
	select( wpm, instance ) {
		wpm.on( 'select', () => {
			let els = [],
			    ids = [];

			const sel = wpm
			        .state()
			        .get( 'selection' )
			        .toArray();

			for ( const i in sel ) {
				const atts = sel[ i ].attributes;
				ids[ i ] = atts.id;
				els[ i ] = this.createEl( wpm, atts );
			}

			ids = JSON.stringify( ids );
			this.setMedia( instance, 'add', els, ids );
		} );
	},


	/**
	 * Обработчик открыия медиа библиотеки.
	 * Помечаем раннее выбранные медиа файлы если они есть.
	 *
	 * @param Object wpm - WP Media Object.
	 * @param DOM Object instance - родительский элемент мета поля "media button".
	 *
	 * @return {void}
	 */
	open( wpm, instance ) {
		wpm.on( 'open', () => {
			const sel = wpm.state().get( 'selection' ),
			      ids = instance.querySelector( this.selectors.input ).value;

			if ( ids ) {
				JSON.parse( ids )
					.forEach( id => {
						const attachment = wp.media.attachment( id );
						attachment.fetch();
						sel.add( attachment ? [ attachment ] : [] );
					} );
			}
		} );
	},


	/**
	 * Добавление медиа данных.
	 *
	 * @param DOM Object instance - родительский элемент мета поля "media button".
	 * @param String action - действие которое нужно выполнить - добавить или удалить медиа файлы.
	 * @param Array els - HTML добавляемых элементов.
	 * @param Array ids - WP идентификаторы добавляемых медиа файлов.
	 *
	 * @return void
	 */
	setMedia( instance, action, els = '', ids = '' ) {
		const mediaPlace = instance.querySelector( this.selectors.mediaPlace );

		while ( mediaPlace.firstChild ) {
			mediaPlace.removeChild( mediaPlace.firstChild );
		}

		for ( const i in els ) {
			mediaPlace.insertAdjacentHTML( 'beforeend', els[ i ] );
		}

		instance.querySelector( this.selectors.input ).setAttribute( 'value', ids );
		this.btnHandler( instance, action );
	},


	/**
	 * Удаление медиа данных.
	 *
	 * @param DOM Object instance - родительский элемент мета поля "media button".
	 *
	 * @return {void}
	 */
	del( instance ) {
		instance.querySelector( this.selectors.btn.del ).addEventListener( 'click', () => {
			this.setMedia( instance, 'del' );
		}, false );
	},


	/**
	 * Изменение текста кнопок при нажатии на них.
	 *
	 * @param DOM Object instance - родительский элемент мета поля "media button".
	 * @param String action  - действие которое нужно выполнить - добавить или удалить медиа файлы.
	 *
	 * @return {void}
	 */
	btnHandler( instance, action ) {
		const addBtn = instance.querySelector( this.selectors.btn.add ),
		      delBtn = instance.querySelector( this.selectors.btn.del );

		if ( 'add' == action ) {
			const stage = addBtn.dataset.stage;
			if ( 'addidable' == stage ) {
				addBtn.dataset.stage = 'edidable';
				addBtn.textContent = addBtn.dataset.actionText;
				delBtn.classList.add( this.selectors.activeClass );
			}
		} else {
			addBtn.dataset.stage = 'addidable';
			addBtn.textContent = delBtn.dataset.actionText;
			delBtn.classList.remove( this.selectors.activeClass );
		}
	},


	/**
	 * Инициализация основных методов.
	 *
	 * @param String wrap - селектор родительского элемента мета поля "media button".
	 *
	 * @return {void}
	 */
	init( wrap ) {
		if ( ! wrap )
			return;

		document.querySelectorAll( wrap ).forEach( instance => {
			this.add( instance );
			this.del( instance );
		} );
	}
};
