<?php

class SALFWP_ConfigurationPage {

	static function getPage()
	{
		?>
		<div class="wrap salfwp-config">
			<form action="<?php echo admin_url('admin.php?page=' . $GLOBALS['SALFWP']->pluginSlug)?>" method="post">
				<div class="salfwp-header">
					<h1 class="heading-top"><strong><?php echo sprintf( esc_html__( '%s', 'salfwp' ), $GLOBALS['SALFWP']->pluginName ) ?></strong> <small><?php echo sprintf( esc_html__( 'v %s', 'salfwp' ), $GLOBALS['SALFWP']->pluginVersion ) ?></small></h1>
					<div class="salfwp-header-link">
						<a class="link-button" href="https://www.wddportfolio.com/contact-us/" target="_blank"><i class="dashicons dashicons-sos"></i> <?php _e( 'Support Portal', 'salfwp' ) ?></a>
					</div>
				</div>

				<div class="salfwp-body">

					<?php echo self::drawOptions(array(
						array( 
							'type'			=> 'dropdown', 
							'name'			=> 'salfwp-api-mode', 
							'title'			=> esc_html__( 'API Mode', 'salfwp' ), 
							'description'	=> esc_html__( 'Choose between Product Advertising API v5 or v4', 'salfwp' ), 
							'option'		=> [
								'lagacy'	=> esc_html__( 'Lagacy Mode', 'salfwp' ),
								'paapi5'	=> esc_html__( 'Product Advertising API - v5', 'salfwp' )
							]
						),
						array( 
							'type'			=> 'text', 
							'name'			=> 'salfwp-tag-id', 
							'title'			=> esc_html__( 'Access Tag ID', 'salfwp' ), 
							'description'	=> esc_html__( 'Enter your Amazon Tag ID', 'salfwp' ), 
						),
						array( 
							'type'			=> 'text', 
							'name'			=> 'salfwp-access-key-id', 
							'title'			=> esc_html__( 'Access Key ID', 'salfwp' ), 
							'description'	=> esc_html__( 'Enter your Amazon Access Key ID', 'salfwp' ), 
						),
						array( 
							'type'			=> 'password', 
							'name'			=> 'salfwp-access-key-secret', 
							'title'			=> esc_html__( 'Access Key Secret', 'salfwp' ), 
							'description'	=> esc_html__( 'Enter your Amazon Access Key Secret', 'salfwp' ), 
						),
						array( 
							'type'			=> 'toggle', 
							'name'			=> 'salfwp-load-css-scripts', 
							'title'			=> esc_html__( 'Load Stylesheet and Scripts', 'salfwp' ), 
							'description'	=> esc_html__( 'When this option is on, this plugin add own pre-defined stylesheet and script to the frontend. When it is off, you have to add your own stylesheet.', 'salfwp' )
						),
					));?>

					<div class="salfwp-option-section salfwp-option-section-button">
						<button type="submit" class="salfwp-option-button"><i class="dashicons dashicons-yes-alt"></i> <?php _e( 'Save Settings', 'salfwp' );?></button>
					</div>

					<input type="hidden" name="nonce"  class="salfwp-hidden-control" value="<?php echo wp_create_nonce( $GLOBALS['SALFWP']->pluginSlug ); ?>">
					<input type="hidden" name="action" class="salfwp-hidden-control" value="salfwp-save">

				</div>
			</form>
		</div>
		<?php
	}

	private static function drawOptions(array $options)
	{

		$output = '';

		$optionValues = $GLOBALS['SALFWP']->getSettings();

		foreach( $options as $option )
		{
	        $function   = str_replace(' ', '', ucwords(str_replace('_', ' ', $option['type'])));
	        $optionFunc = 'drawOption'.$function;

			//$output .= '<pre>'.print_r($option, true).'</pre>';
			$output .= '<div class="salfwp-option-section"><div class="salfwp-option-section-info"><h4 class="salfwp-option-section-info-name"><label for="'.$option['name'].'">'.$option['title'].'</label></h4>';
			if ( isset($option['description']) && !empty($option['description']) ){
				$output .= '<div class="salfwp-option-section-info-caption">'.$option['description'].'</div>';
			}
	        $output .= '</div><div class="salfwp-option-section-container" data-function="'.$optionFunc.'">';

	        if ($option['type'] == 'dropdown'){
	        	$output .= self::{$optionFunc}( $option['name'], $optionValues[ $option['name'] ], $option['option'] );
	        }else{
	        	$output .= self::{$optionFunc}( $option['name'], $optionValues[ $option['name'] ] );
	        }

	        $output .= '</div></div>';
		}

		return $output;

	}

	private static function drawOptionText($option_name, $option_value='')
	{
		return self::drawOption($option_name, $option_value);
	}

	private static function drawOptionPassword($option_name, $option_value='')
	{
		return self::drawOption($option_name, $option_value, 'password');
	}

	private static function drawOption($option_name, $option_value='', $type='text')
	{
		return "<input type=\"{$type}\" name=\"{$option_name}\" value=\"{$option_value}\" id=\"{$option_name}\" class=\"salfwp-input-field\">";
	}

	private static function drawOptionToggle($option_name, $option_value='')
	{
		$option_on  = esc_html__( 'ON' , 'salfwp' );
		$option_off = esc_html__( 'OFF', 'salfwp' );
		return "<input type=\"checkbox\" name=\"{$option_name}\" value=\"true\" id=\"{$option_name}\" class=\"salfwp-input-toggle\" ".checked( (boolean) $option_value, true, false )."><label for=\"{$option_name}\" data-on=\"{$option_on}\" data-off=\"{$option_off}\"><i></i></label>";
	}

	private static function drawOptionDropdown($option_name, $option_value='', $option_dropdown = array())
	{
		$output  = "<select name=\"{$option_name}\" value=\"true\" id=\"{$option_name}\" class=\"salfwp-dropdown\">";
		//$output .= '<option value="">' . esc_html__( 'Select One', 'salfwp' ) . '</option>';
		foreach ($option_dropdown as $option_id=>$option_text){
			$output .= '<option value="' . $option_id . '" '. selected($option_id, $option_value, false) .'>' . $option_text . '</option>';
		}
		$output .= "</select>";
		return $output;
	}
}