( function( wp ) {
	
	var registerBlockType = wp.blocks.registerBlockType;
	
	var el = wp.element.createElement,
	SelectControl = wp.components.SelectControl,
	Fragment = wp.element.Fragment,
	InspectorControls = wp.blockEditor.InspectorControls;
	
	var options = [];
	var restaurants = js_data;
	
	const { __ } = wp.i18n;

	const orderingIcon = el('svg', 
		{ 
			width: 18, 
			height: 18 
		},
		el( 'path',
		{ 
		d: "M15.1863 11.02C15.1863 10.3751 14.6742 9.86301 14.0293 9.86301C13.6121 9.86301 13.2517 10.0717 13.043 10.4131V10.3751C13.043 9.73024 12.5309 9.21812 11.886 9.21812C11.4687 9.21812 11.1084 9.42676 10.8997 9.76817V9.74921C10.8997 9.10432 10.3876 8.5922 9.74272 8.5922C9.32544 8.5922 8.96506 8.80084 8.75642 9.14225V5.23498C8.75642 4.9315 8.64261 4.62802 8.41501 4.41938C8.1874 4.21074 7.90289 4.07797 7.59941 4.07797C6.95452 4.07797 6.4424 4.59009 6.4424 5.23498V12.3857C6.4424 12.4615 6.40447 12.4995 6.34757 12.5374C6.29066 12.5564 6.21479 12.5564 6.17686 12.4995L5.00089 11.3235C4.60257 10.9252 3.97665 10.8683 3.52143 11.1907C3.25589 11.3804 3.06622 11.6649 3.00931 11.9873C2.97138 12.3098 3.04725 12.6512 3.27486 12.8978L6.19583 16.4257C7.03039 17.431 8.2443 18 9.53408 18H11.3739C12.3981 18 13.3465 17.6017 14.0673 16.8809C14.788 16.1602 15.1863 15.2118 15.1863 14.1876V11.02Z M4 2H14C15.1046 2 16 2.89543 16 4V6C16 6.89968 15.406 7.66061 14.5887 7.91195C15.3794 8.05211 16.0664 8.48449 16.5349 9.09435C17.4294 8.36077 18 7.24703 18 6V4C18 1.79086 16.2091 0 14 0H4C1.79086 0 0 1.79086 0 4V6C0 7.59631 0.935084 8.97422 2.28738 9.61586C2.31103 9.59799 2.33489 9.58045 2.35896 9.56326L2.36539 9.55866C2.99248 9.11448 3.72858 8.93278 4.4424 8.99318V8H4C2.89543 8 2 7.10457 2 6V4C2 2.89543 2.89543 2 4 2Z" 
		}
		)
	);

	for (x in restaurants) {
		options.push( { 
			label: restaurants[x]['name'],value: restaurants[x]['uid'] } );
	} 

	registerBlockType( 'menu-ordering-reservations/menu-ordering', {
		
		title: __( 'Ordering', 'menu-ordering-reservations' ),
		
		icon: orderingIcon,

		category: 'widgets',

		supports: {
			html: false,
		},
		attributes:  {
			ruid : {
				default: restaurants[0]['uid'],
				type: 'string'
				
			}
		},

		edit: function( props ) {
	
		const attributes =  props.attributes;
		const {serverSideRender: ServerSideRender} = wp;
		
		var content = props.attributes.content,
			ruid = props.attributes.ruid;
			
	
			function onChangeSelectField( newValue ) {
				props.setAttributes( { ruid: newValue } );
			}

			return (
				el(
					Fragment,
					null,
					el(
						InspectorControls,
						null,
												
						el(
							SelectControl,
							{
								label: __( 'Select restaurant', 'menu-ordering-reservations' ),
								value: ruid,
								options: options,
								onChange: onChangeSelectField,
								className: "gblock"
							}
						)
					),
					el('div', {}, [
						//Preview a block with a PHP render callback
						el( ServerSideRender, {
							block: 'menu-ordering-reservations/menu-ordering',
							attributes: attributes
							} ),
						])
					)
				);

		},

	
		save: function(props) {
			var content = props.attributes.content,
			ruid = props.attributes.ruid;
		
		return null;
		}
	} );

} )(
	window.wp
);
