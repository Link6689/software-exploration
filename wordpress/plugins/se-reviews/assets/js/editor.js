/**
 * Editor UI for the Software Exploration blocks. Built from window.SE_BLOCKS
 * (declared in includes/blocks.php): settings in the sidebar, live preview of
 * the PHP output in the canvas. No build step.
 */
( function ( wp, config ) {
	if ( ! wp || ! config ) {
		return;
	}
	var el = wp.element.createElement;
	var be = wp.blockEditor;
	var C = wp.components;
	var useSelect = wp.data.useSelect;
	var decode = wp.htmlEntities.decodeEntities;
	var ServerSideRender = wp.serverSideRender;

	function useTools() {
		return useSelect( function ( select ) {
			return select( 'core' ).getEntityRecords( 'postType', 'se_tool', {
				per_page: 100,
				status: 'publish',
				orderby: 'title',
				order: 'asc',
				_fields: 'id,title',
			} ) || [];
		}, [] );
	}

	function toolName( t ) {
		return decode( ( t.title && t.title.rendered ) || '#' + t.id );
	}

	function control( key, def, props, tools ) {
		var value = props.attributes[ key ];
		function set( v ) {
			var o = {};
			o[ key ] = v;
			props.setAttributes( o );
		}
		var common = { key: key, label: def.label, help: def.help, __nextHasNoMarginBottom: true };

		switch ( def.control ) {
			case 'tool':
				return el( C.SelectControl, Object.assign( {}, common, {
					value: String( value || 0 ),
					options: [ { label: '—', value: '0' } ].concat( tools.map( function ( t ) {
						return { label: toolName( t ), value: String( t.id ) };
					} ) ),
					onChange: function ( v ) { set( parseInt( v, 10 ) || 0 ); },
				} ) );
			case 'tools':
				var byId = {}, byName = {};
				tools.forEach( function ( t ) {
					byId[ t.id ] = toolName( t );
					byName[ toolName( t ) ] = t.id;
				} );
				return el( C.FormTokenField, {
					key: key,
					label: def.label,
					value: ( value || [] ).map( function ( id ) { return byId[ id ] || '#' + id; } ),
					suggestions: Object.keys( byName ),
					__experimentalExpandOnFocus: true,
					__nextHasNoMarginBottom: true,
					onChange: function ( names ) {
						set( names.map( function ( n ) { return byName[ n ]; } ).filter( Boolean ) );
					},
				} );
			case 'toggle':
				return el( C.ToggleControl, Object.assign( {}, common, {
					checked: !! value,
					onChange: function ( v ) { set( !! v ); },
				} ) );
			case 'textarea':
				return el( C.TextareaControl, Object.assign( {}, common, {
					value: value || '',
					rows: 6,
					onChange: set,
				} ) );
			case 'number':
				return el( C.TextControl, Object.assign( {}, common, {
					type: 'number',
					value: value == null ? '' : String( value ),
					onChange: function ( v ) { set( parseInt( v, 10 ) || 0 ); },
				} ) );
			case 'date':
				return el( C.TextControl, Object.assign( {}, common, {
					type: 'date',
					value: value || '',
					onChange: set,
				} ) );
			case 'select':
				return el( C.SelectControl, Object.assign( {}, common, {
					value: value,
					options: ( def.options || [] ).map( function ( o ) { return { label: o === '' ? '—' : o, value: o }; } ),
					onChange: set,
				} ) );
			default:
				return el( C.TextControl, Object.assign( {}, common, { value: value || '', onChange: set } ) );
		}
	}

	Object.keys( config ).forEach( function ( name ) {
		var b = config[ name ];
		wp.blocks.registerBlockType( 'se/' + name, {
			apiVersion: 3,
			title: b.title,
			description: b.desc,
			icon: b.icon,
			category: 'se-reviews',
			attributes: b.attributes,
			supports: { html: false, align: [ 'wide', 'full' ] },
			edit: function ( props ) {
				var tools = useTools();
				var postId = useSelect( function ( select ) {
					var editor = select( 'core/editor' );
					return editor ? editor.getCurrentPostId() : 0;
				}, [] );
				var controls = Object.keys( b.controls );
				return el(
					'div',
					be.useBlockProps(),
					controls.length > 0 ? el(
						be.InspectorControls,
						null,
						el( C.PanelBody, { title: b.title },
							el( C.Flex, { direction: 'column', gap: 4 },
								controls.map( function ( k ) { return control( k, b.controls[ k ], props, tools ); } ) ) )
					) : null,
					el( ServerSideRender, {
						block: 'se/' + name,
						attributes: props.attributes,
						urlQueryArgs: postId ? { post_id: postId } : {},
						EmptyResponsePlaceholder: function () {
							return el( 'div', { className: 'se se-placeholder' }, b.title );
						},
					} )
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp, window.SE_BLOCKS );
