( function( wp ) {
	
	var registerBlockType = wp.blocks.registerBlockType;
	
	var el = wp.element.createElement,
	SelectControl = wp.components.SelectControl,
	Fragment = wp.element.Fragment,
	InspectorControls = wp.blockEditor.InspectorControls;
	
	var options = [];
	var restaurants = js_data;
		
	const { __ } = wp.i18n;

	const menuIcon = el('svg', 
		{ 
			width: 18, 
			height: 16 
		},
		el( 'path',
		{ 
		d: "M0.421875 5.625C0.189844 5.625 0 5.43516 0 5.20312V3.79688C0 3.56484 0.189844 3.375 0.421875 3.375H1.2V1.6875C1.2 0.755859 1.94836 0 2.88 0H14.3164C15.248 0 16.0039 0.755859 16.0039 1.6875V16.3125C16.0039 17.2441 15.248 18 14.3164 18H2.88C1.94836 18 1.2 17.2441 1.2 16.3125V14.625H0.421875C0.189844 14.625 0 14.4352 0 14.2031V12.7969C0 12.5648 0.189844 12.375 0.421875 12.375H1.2V10.125H0.421875C0.189844 10.125 0 9.93516 0 9.70312V8.29688C0 8.06484 0.189844 7.875 0.421875 7.875H1.2V5.625H0.421875ZM11.2177 14.9997L11.1934 15C10.755 15.0005 10.4 14.6559 10.4 14.2299V3.43109C10.4 3.33856 10.4078 3.18496 10.4591 3.10643C10.8754 2.47217 12.8677 4.78254 13 7.12464C13 8.24196 12.8865 9.15421 12.0312 9.53661C11.9447 9.57514 11.8741 9.6754 11.8768 9.76767L11.9914 14.2272C12.0021 14.6531 11.6559 14.9988 11.2177 14.9997ZM4.4 5.8529C4.4 7.04184 4.97027 8.08078 5.85107 8.45811C5.94041 8.49664 6.01334 8.59865 6.01027 8.69598L5.8404 14.1203C5.82827 14.606 6.21214 15 6.69814 15C7.18388 15 7.56801 14.606 7.55574 14.1203L7.38574 8.69598C7.38281 8.59878 7.45561 8.49664 7.54494 8.45838C8.42588 8.08104 8.99615 7.04211 8.99615 5.8529C8.99615 4.35543 8.48095 3.04476 8.11814 3.00076C7.82854 2.98623 7.71334 3.18223 7.71334 3.40569L7.71308 5.78437C7.71308 5.93797 7.59161 6.0629 7.44254 6.0629H7.42628C7.27708 6.0629 7.15574 5.93797 7.15574 5.78437L7.15521 3.40569C7.15521 3.18223 6.97587 3.00076 6.75907 3.00076H6.63721C6.42041 3.00076 6.24107 3.18223 6.24107 3.40569L6.24054 5.78437C6.24054 5.93797 6.11921 6.0629 5.97014 6.0629H5.95374C5.80454 6.0629 5.6832 5.93797 5.6832 5.78437L5.68294 3.40569C5.68294 3.18223 5.56787 2.98623 5.278 3.00076C4.91547 3.04476 4.4 4.35556 4.4 5.8529Z" 
		}
		)
	);

	for (x in restaurants) {
		options.push( { 
			label: restaurants[x]['name'],value: restaurants[x]['uid'] } );
	} 
	
	registerBlockType( 'menu-ordering-reservations/food-menu', {
		
		title: __( 'Food Menu', 'menu-ordering-reservations' ),

		icon: menuIcon,

		category: 'widgets',

		
		supports: {
			// Removes support for an HTML mode.
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
								block: 'menu-ordering-reservations/food-menu',
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
