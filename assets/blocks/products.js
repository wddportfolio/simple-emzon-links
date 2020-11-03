/**
 * WordPress dependencies
 */
const { __ } = window.wp.i18n;
const { registerBlockType } = window.wp.blocks;
const { createElement, Fragment } = window.wp.element;
const { TextControl, SelectControl, Panel, PanelBody, PanelRow } = window.wp.components;
const { RichText, InspectorControls } = window.wp.blockEditor;

const salfwpIcon = createElement('svg', 
	{ 
		width: 24, 
		height: 24 
	},
	createElement( 'path', { d: "M18.42 14.58c-.51-.66-1.05-1.23-1.05-2.5V7.87c0-1.8.15-3.45-1.2-4.68-1.05-1.02-2.79-1.35-4.14-1.35-2.6 0-5.52.96-6.12 4.14-.06.36.18.54.4.57l2.66.3c.24-.03.42-.27.48-.5.24-1.12 1.17-1.63 2.2-1.63.56 0 1.22.21 1.55.7.4.56.33 1.31.33 1.97v.36c-1.59.18-3.66.27-5.16.93a4.63 4.63 0 0 0-2.93 4.44c0 2.82 1.8 4.23 4.1 4.23 1.95 0 3.03-.45 4.53-1.98.51.72.66 1.08 1.59 1.83.18.09.45.09.63-.1v.04l2.1-1.8c.24-.21.2-.48.03-.75zm-5.4-1.2c-.45.75-1.14 1.23-1.92 1.23-1.05 0-1.65-.81-1.65-1.98 0-2.31 2.1-2.73 4.08-2.73v.6c0 1.05.03 1.92-.5 2.88z" } ),
	createElement( 'path', { d: "M21.69 19.2a17.62 17.62 0 0 1-21.6-1.57c-.23-.2 0-.5.28-.33a23.88 23.88 0 0 0 20.93 1.3c.45-.19.84.3.39.6z" } ),
	createElement( 'path', { d: "M22.8 17.96c-.36-.45-2.22-.2-3.1-.12-.23.03-.3-.18-.05-.36 1.5-1.05 3.96-.75 4.26-.39.3.36-.1 2.82-1.5 4.02-.21.18-.42.1-.3-.15.3-.8 1.02-2.58.69-3z" } ),
);
 
const blockStyle = {
    backgroundColor: '#900',
    color: '#fff',
    padding: '20px',
};
 
registerBlockType( 'salfwp/amazon', {
    title: __('Amazon Products', 'salfwp'),
    description: __('Embed Amazon Products to your page.', 'salfwp'),
    icon: salfwpIcon,//'<svg width="24" height="24" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false"><path d="M18.42 14.58c-.51-.66-1.05-1.23-1.05-2.5V7.87c0-1.8.15-3.45-1.2-4.68-1.05-1.02-2.79-1.35-4.14-1.35-2.6 0-5.52.96-6.12 4.14-.06.36.18.54.4.57l2.66.3c.24-.03.42-.27.48-.5.24-1.12 1.17-1.63 2.2-1.63.56 0 1.22.21 1.55.7.4.56.33 1.31.33 1.97v.36c-1.59.18-3.66.27-5.16.93a4.63 4.63 0 0 0-2.93 4.44c0 2.82 1.8 4.23 4.1 4.23 1.95 0 3.03-.45 4.53-1.98.51.72.66 1.08 1.59 1.83.18.09.45.09.63-.1v.04l2.1-1.8c.24-.21.2-.48.03-.75zm-5.4-1.2c-.45.75-1.14 1.23-1.92 1.23-1.05 0-1.65-.81-1.65-1.98 0-2.31 2.1-2.73 4.08-2.73v.6c0 1.05.03 1.92-.5 2.88z"></path><path d="M21.69 19.2a17.62 17.62 0 0 1-21.6-1.57c-.23-.2 0-.5.28-.33a23.88 23.88 0 0 0 20.93 1.3c.45-.19.84.3.39.6z"></path><path d="M22.8 17.96c-.36-.45-2.22-.2-3.1-.12-.23.03-.3-.18-.05-.36 1.5-1.05 3.96-.75 4.26-.39.3.36-.1 2.82-1.5 4.02-.21.18-.42.1-.3-.15.3-.8 1.02-2.58.69-3z"></path></svg>',
    category: 'embed',
    supports: {
		customClassName: true,
		className: 'wp-block-salfwp-amazon',
		html: false
	},
	keywords: ['amazon', 'affiliate', 'amazon product', 'product'],
	attributes: {
		productIds: {
			type: 'string'
		},
	},

	transforms: {
		from: [{
			type: 'shortcode',
			tag: ['salfwp', 'salfwp'],
			attributes: {
				layoutType: {
					type: 'string',
					shortcode: ({ named: { layout } }) => {
						return isNaN(layout) ? 'listing' : layout;
					}
				},
				productIds: {
					type: 'string',
					shortcode: ({ named: { product_ids } }) => {
						return isNaN(product_ids) ? null : product_ids;
					}
				},
			}
		}]
	},


    // Determines what is displayed in the editor
    edit: function( props ) {
		return (
			createElement( Fragment, {},
				createElement( PanelRow, {className: 'salfwp-amazon-product'},

					// Textarea field
					createElement( TextControl,
						{
							label: __('Product ID', 'salfwp'),
							onChange: ( value ) => {
								props.setAttributes( { productIds: value } );
							},
							value: props.attributes.productIds,
						}
					)
				),
			)
		);
	 
	},
    // Determines what is displayed on the frontend
    save: function( props ) {
		return ;
	}
} );