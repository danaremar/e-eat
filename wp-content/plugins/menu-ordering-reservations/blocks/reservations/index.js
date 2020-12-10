( function( wp ) {

	
	
	var registerBlockType = wp.blocks.registerBlockType;
	
	var el = wp.element.createElement,
	SelectControl = wp.components.SelectControl,
	Fragment = wp.element.Fragment,
	InspectorControls = wp.blockEditor.InspectorControls;
	
	var options = [];
	var restaurants = js_data;
		
	const { __ } = wp.i18n;

	const reservationsIcon = el('svg', 
		{ 
			width: 18, 
			height: 16 
		},
		el( 'path',
		{ 
		d: "M17.9973 3.56262C17.8785 2.03056 13.8958 0.800079 9 0.800079C4.10424 0.800079 0.121487 2.03056 0.00272695 3.56262C0.000912491 3.53933 0 3.51596 0 3.49253V4.72205C0 6.22779 3.7323 7.46616 8.44357 7.55714C8.21148 8.68734 7.81199 10.8531 7.76927 12.5545C7.41085 12.5885 7.05399 12.6384 6.69976 12.704C6.63395 12.7157 6.56963 12.729 6.5083 12.7436C6.04385 12.8301 5.59482 12.9829 5.17558 13.1973C5.16295 13.2051 5.15014 13.2124 5.13741 13.2198C5.11395 13.2333 5.0907 13.2467 5.06938 13.2619C4.7807 13.4337 4.62065 13.6363 4.62065 13.8492C4.62088 13.8874 4.62642 13.9255 4.6371 13.9622C4.64528 13.9994 4.65942 14.0351 4.67898 14.0679C4.99609 14.6846 6.66536 15.1617 8.71754 15.1999H9.06007C9.81268 15.2011 10.5638 15.1337 11.3037 14.9988L11.3528 14.9891C11.4015 14.9795 11.4492 14.9701 11.4952 14.9591C11.9605 14.8722 12.4104 14.7194 12.8309 14.5055C12.8678 14.487 12.9033 14.4659 12.9371 14.4423C13.2228 14.2632 13.3858 14.0606 13.3858 13.8448C13.388 13.8119 13.3865 13.7789 13.3813 13.7464C13.3734 13.7098 13.3598 13.6747 13.3409 13.6422C13.0776 13.1202 11.8488 12.6963 10.2376 12.5513C10.1942 10.8506 9.79515 8.68658 9.56328 7.55704C14.2713 7.46552 18 6.23454 18 4.72952V3.49253C18 3.51596 17.9991 3.53933 17.9973 3.56262Z" 
		}
		)
	);

	for (x in restaurants) {
		options.push( { 
			label: restaurants[x]['name'],value: restaurants[x]['uid'] } );
	} 

	registerBlockType( 'menu-ordering-reservations/reservations', {

       
		title: __( 'Reservations', 'menu-ordering-reservations' ),

		icon: reservationsIcon,

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
						block: 'menu-ordering-reservations/reservations',
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
