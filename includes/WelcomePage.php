<?php

class SALFWP_WelcomePage {

	static function aboutPage()
	{
		?>
		<div class="wrap salfwp-config">
			<div class="salfwp-header">
				<h1 class="heading-top">About <strong><?php echo sprintf( esc_html__( '%s', 'salfwp' ), $GLOBALS['SALFWP']->pluginName ) ?></strong> <small><?php echo sprintf( esc_html__( 'v %s', 'salfwp' ), $GLOBALS['SALFWP']->pluginVersion ) ?></small></h1>
				<div class="salfwp-header-link">
					<a class="link-button" href="https://www.wddportfolio.com/contact-us/" target="_blank"><i class="dashicons dashicons-sos"></i> <?php _e( 'Support Portal', 'salfwp' ) ?></a>
				</div>
			</div>

			<div class="salfwp-body">
				<div class="wrap about-wrap salfwp-home">
					<p><?php echo esc_html__( "We made every small element, motion and interaction so neat and lovely, that you can entirely focus on the big picture. Hope you'll enjoy it the same as we do!", 'salfwp' );?></p>

					<p><?php echo sprintf( __( "Thanks to %s for their time and great efforts to develop this WordPress Plugin. We appreciate it very much.", 'salfwp' ), '<a href="https://www.mfurqanabid.com/" target="_blank"><strong>M. Furqan Abid</strong></a>');?></p>

					<p><?php _e( "You can follow us on the following platforms:", 'salfwp' );?></p>

					<ul>
						<li><?php echo sprintf( __('Follow %s on <a href="%s" target="_blank">Twitter</a>', 'salfwp' ), 'WDDPortfolio', 'https://twitter.com/wddportfolio');?></a></li>
						<li><?php echo sprintf( __('Follow WDDPortfolio on <a href="%s" target="_blank">Facebook</a>', 'salfwp' ), 'https://www.facebook.com/wddportfolio');?></a></li>
						<li><?php echo sprintf( __('Follow %s on <a href="%s" target="_blank">Twitter</a>', 'salfwp' ), 'M. Furqan Abid', 'https://twitter.com/furqanabid');?></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

}