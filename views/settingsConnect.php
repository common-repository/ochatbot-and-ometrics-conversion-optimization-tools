<div class="wrap">
  <h2><?php echo $this->plugin->displayName; ?> &raquo; <?php _e( 'Settings', $this->plugin->name ); ?></h2>

  <?php
  if ( isset( $this->message ) ) {
    ?>
    <div class="updated fade"><p><?php echo $this->message; ?></p></div>
    <?php
  }
  if ( isset( $this->errorMessage ) ) {
    ?>
    <div class="error fade"><p><?php echo $this->errorMessage; ?></p></div>
    <?php
  }

  ?>

  <div id="ometrics-settings-connect">
    <div id="post-body" class="metabox-holder columns-2">
      <!-- Content -->
      <div id="post-body-content">
        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
          <div class="postbox">
            <img src="<?php echo plugin_dir_url( __DIR__ ); ?>assets/images/ochatbot-mark-128x128.jpg" class="ochatbot-logo" alt="Ochatbot" /><h3 class="hndle"><?php _e( 'Connect your Ometrics Account', $this->plugin->name ); ?></h3>

            <div class="inside ometrics-section">
              <div class="ometrics-col ometrics-grid_1_of_2">
                <div class="ometrics-tab" style="<?php echo (!$this->settings['ometrics_token'] || !$this->settings['ometrics_id']) ? 'display:block;' : 'display:none;'; ?>">
                  <button id="register-account-button" class="ometrics-tablinks active" onclick="openTab(event, 'register-account')"><span id="register-button">Register Account</span></button>
                  <button id="login-button" class="ometrics-tablinks" onclick="openTab(event, 'login')">Login</button>
                </div>
                <div id="disconnect" class="ometrics-tabcontent ometrics-connected" style="<?php echo ($this->settings['ometrics_token'] && $this->settings['ometrics_id']) ? 'display:block;' : 'display:none;'; ?>">
                  <div class="ometrics-disconnect-display">
                    <p style="font-size:2em!important; color:rgb(104,172,55)!important;"><?php _e( 'Your Ometrics Account is Connected!', $this->plugin->name ); ?></p>

                    <p style="margin-bottom: 40px;font-size:1.7em;"><?php _e( 'Configure your Ochatbot, view reports &amp; more by visiting <a href="https://www.ometrics.com/user">your Dashboard at Ometrics.com.</a>', $this->plugin->name ); ?></p>
                    <p><?php _e( 'If you wish to disconnect from your Ometrics account, click this button.', $this->plugin->name ); ?></p>
                    <button id="ometrics-disconnect">Disconnect from Ometrics Account</button>
                    <p style="font-size: 110%;">
                      <?php _e( '<b>Note: The Ometrics tools and Ochatbot installed on your site will no longer function after you disconnect your account.</b>', $this->plugin->name ); ?>
                    </p>
                  </div>
                </div>
                <div id="login" class="ometrics-tabcontent" style="display:none;">
                  <form id="ometrics-connect-account" style="<?php echo (!$this->settings['ometrics_token'] || !$this->settings['ometrics_id']) ? 'display:block;' : 'display:none;'; ?>">
                    <p style="font-size: 1.3em;">
                      <?php _e( 'Enter your Ometrics account credentials and click the button to connect your account.', $this->plugin->name ); ?>
                      <br /><br />
                      <?php _e( 'Don\'t have an Ometrics account yet?  <a onclick="openTab(event, \'register-account\')" style="cursor: pointer; font-weight:bold;">Get your FREE account here.</a>', $this->plugin->name ); ?>
                    </p>
                    <p>
                      <label for="ometrics_user"><strong><?php _e( 'Ometrics User Name', $this->plugin->name ); ?></strong></label>
                      <input type="text" size="32" name="email" id="ometrics_user" class="ometrics-input" /><br />
                      <?php _e( '(This is your Ometrics Account Email Address.)', $this->plugin->name ); ?>
                    </p>
                    <p>
                      <label for="ometrics_password"><strong><?php _e( 'Ometrics Password', $this->plugin->name ); ?></strong></label>
                      <input type="password" size="64" name="password" id="ometrics_password" class="ometrics-input" /><br />
                      <?php _e( '(This is your Ometrics Account Password.)', $this->plugin->name ); ?>
                    </p>
                    <p>
                      <?php wp_nonce_field( $this->plugin->name, $this->plugin->name.'_nonce' ); ?>
                      <input name="submit" type="submit" value="Connect My Ometrics Account" class="button button-primary button-ometrics"  />
                    </p>
                    <input type="hidden" name="domain" id="domain" value="<?php echo get_site_url( null, '', 'https' ); ?>" />
                    <input type="hidden" name="woo_commerce" id="woo_commerce" value="<?php echo class_exists( 'woocommerce' ) ? '1' : '0' ?>" />
                    <input type="hidden" name="type" id="form_type" value="connect" />
                    <input type="hidden" id="form_action" name="action" value="ometrics_submit" />

                  </form>
                  <div id="forgot-password-section" style="<?php echo (!$this->settings['ometrics_token'] || !$this->settings['ometrics_id']) ? 'display:block;' : 'display:none;'; ?>">
                    <p><a id="ometrics-forgot">Forgot Your Ometrics Password?</a></p>
                    <form id="ometrics-forgot-form" style="display:none;">
                      <p style="font-size: 1.3em;">
                        <?php _e( 'Enter your Ometrics account email address.', $this->plugin->name ); ?>
                      </p>
                      <p>
                        <label for="ometrics_user"><strong><?php _e( 'Ometrics User Name', $this->plugin->name ); ?></strong></label>
                        <input type="text" size="32" name="email" id="ometrics_user" class="ometrics-input" /><br />
                        <?php _e( '(This is your Ometrics Account Email Address.)', $this->plugin->name ); ?>
                      </p>
                      <p>
                        <input name="submit" type="submit" value="Reset My Ometrics Password" class="button button-primary button-ometrics"  />
                        <input type="hidden" name="type" id="form_type" value="forgot" />
                        <?php wp_nonce_field( $this->plugin->name, $this->plugin->name.'_nonce' ); ?>
                        <input type="hidden" id="form_action" name="action" value="ometrics_submit" />
                      </p>
                    </form>
                  </div>

                  <form action="options-general.php?page=<?php echo $this->plugin->name; ?>" method="post" id="ometrics-settings-connect-form">
                    <input type="hidden" id="form_action" name="action" value="ometrics_submit" />
                    <p>
                      <label for="ometrics_id"><strong><?php _e( 'Ometrics ID', $this->plugin->name ); ?></strong></label>
                      <input type="text" size="32" maxlength="32" name="ometrics_id" id="ometrics_id" value="<?php echo $this->settings['ometrics_id']; ?>" class="ometrics-input" />
                    </p>
                    <p>
                      <label for="ometrics_token"><strong><?php _e( 'Ometrics Token', $this->plugin->name ); ?></strong></label>
                      <input type="text" size="32" maxlength="32" name="ometrics_token" id="ometrics_token" value="<?php echo $this->settings['ometrics_token']; ?>" class="ometrics-input" />
                    </p>
                    <p>
                      <label for="ometrics_agent"><strong><?php _e( 'Ometrics Chatbot Id', $this->plugin->name ); ?></strong></label>
                      <input type="text" size="32" maxlength="32" name="ometrics_agent" id="ometrics_agent" value="<?php echo $this->settings['ometrics_agent']; ?>" class="ometrics-input" />
                    </p>
                    <?php wp_nonce_field( $this->plugin->name, $this->plugin->name.'_nonce' ); ?>
                    <p>
                      <input name="submit" type="submit" name="Submit" class="button button-primary" value="<?php _e( 'Save', $this->plugin->name ); ?>" />
                    </p>
                    <input type="hidden" name="ometrics_domain" id="ometrics_domain" value="<?php echo get_site_url( null, '', 'https' ); ?>" />

                  </form>
                </div>
                <div id="register-account" class="ometrics-tabcontent" style="<?php echo (!$this->settings['ometrics_token'] || !$this->settings['ometrics_id']) ? 'display:block;' : 'display:none;'; ?>">
                  <form name="registration_form" id="registration_form" class="form-horizontal">
                    <fieldset>
                      <!-- Form Name -->
                      <legend class="form-head">Create Your Free Account</legend>
                      <div class="" style="font-size:1.2em!important;">
                        Already have an Ometrics account? <a onclick="openTab(event, 'login')" style="cursor: pointer;">Log in.</a>
                      </div>
                      <p>
                        <label class="ometrics-label" for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" maxlength="50" placeholder="First Name" class="ometrics-input" value="" />
                        <span id="first_name_err" class="ometrics-error"></span>
                      </p>

                      <p>
                        <label class="ometrics-label" for="last_name">Last Name</label>
                        <input type="text"  name="last_name" id="last_name" maxlength="50" class="ometrics-input" placeholder="Last Name" value="" />
                        <span id="last_name_err" class="ometrics-error"></span>
                      </p>

                      <input type="hidden" name="website_url" id="website_url" value="<?php echo get_site_url( null, '', 'https' ); ?>" />
                      <input type="hidden" name="woo_commerce" value="<?php echo class_exists( 'woocommerce' ) ? '1' : '0' ?>" />
                      <?php if (class_exists( 'woocommerce' )) {
                        $orderby = 'count';
                        $order = 'desc';
                        $hide_empty = true;
                        $cat_args = array(
                            'orderby'    => $orderby,
                            'order'      => $order,
                            'hide_empty' => $hide_empty,
                        );

                        $product_categories = get_terms( 'product_cat', $cat_args );
                        $prodCount = 1;
                        foreach ($product_categories as $key => $category) {
                          if ($prodCount > 3) {
                            //only send top 3 categories
                            break;
                          }
                          ?>
                            <input type="hidden" name="category_<?php echo $prodCount?>_name" value="<?php echo $category->name; ?>" />
                            <input type="hidden" name="category_<?php echo $prodCount?>_url" value="<?php echo get_term_link($category); ?>" />
                          <?php
                          $prodCount++;
                        }
                        $store_address     = get_option( 'woocommerce_store_address' );
                        $store_address_2   = get_option( 'woocommerce_store_address_2' );
                        $store_city        = get_option( 'woocommerce_store_city' );
                        $store_postcode    = get_option( 'woocommerce_store_postcode' );

                        // The country/state
                        $store_raw_country = get_option( 'woocommerce_default_country' );

                        // Split the country/state
                        $split_country = explode( ":", $store_raw_country );

                        // Country and state separated:
                        $store_country = $split_country[0];
                        $store_state   = $split_country[1];
                        $fullAddress = $store_address;
                        if ($store_address_2) {
                          $fullAddress .= ' ' . $store_address_2;
                        }
                        $fullAddress .= ', ' . $store_city . ", " . $store_state . " " . $store_country;
                      ?>
                        <input type="hidden" name="address" value="<?php echo $fullAddress; ?>" />

                      <?php } ?>
                      <input type="hidden" name="company_name" value="<?php echo get_bloginfo('name') ?>" />
                      <input type="hidden" name="contact_email" value="<?php echo get_bloginfo('admin_email') ?>" />

                      <p>
                        <label class="ometrics-label" for="reg_user_email">Email</label>
                        <input type="text"  name="reg_user_email" id="reg_user_email" class="ometrics-input" maxlength="50" placeholder="Business Email" onblur="checkemail(this.value)" value="" />
                        <input type="hidden" name="emailcheckId" id="emailcheckId" value="" />
                        <span id="reg_user_email_err" class="ometrics-error"></span>
                        <span id="user_email_check_err" class="ometrics-error"></span>
                      </p>

                      <p>
                        <label class="ometrics-label" for="reg_user_password">Password</label>
                        <input type="password" name="reg_user_password" id="reg_user_password" class="ometrics-input" />
                        <span id="reg_user_password_err" class="ometrics-error"></span>
                        <span class="small-terms">(Passwords must include eight or more characters, upper and lower case letters and numbers.)</span>
                      </p>

                      <div>
                        <br /><br />
                          <input type="submit" name="Submit" Value="Create My Free Account" class="button button-primary button-ometrics">
                          <p style="margin:0 0; font-size:1.3em!important;">(Credit Card Not Required)</p>
                          <p class="small-terms">By clicking the button above you agree to our <a style="color:#000;text-decoration:underline;" target="_blank" href="https://www.ometrics.com/terms-of-service/">Terms of Service</a>.</p>

                      </div>

                    </fieldset>
                    <input type="hidden" name="membership" id="membership" value="12">
                    <input type="hidden" name="type" id="form_type" value="register" />
                    <?php wp_nonce_field( $this->plugin->name, $this->plugin->name.'_nonce' ); ?>
                    <input type="hidden" id="form_action" name="action" value="ometrics_submit" />
                  </form>


                </div>
              </div>

              <form id="emailForm" style="display:none;">
                <input type="hidden" name="type" id="form_type" value="email" />
                <?php wp_nonce_field( $this->plugin->name, $this->plugin->name.'_nonce' ); ?>
                <input type="hidden" id="form_action" name="action" value="ometrics_submit" />
              </form>

              <div class="ometrics-col ometrics-grid_1_of_2">
                <div class="ometrics-support">
                  <h3>Get Started</h3>
                  <p class="ometrics-headline">We can set up your chatbot for FREE!</p>
                  <p>All you need to do is Contact Us:</p>

                  <ul>
                    <li><a href="https://www.ometrics.com/contact_us">Message us on our contact form</a>.</li>
                    <li><a href="mailto:support@ometrics.com">Send us an email at support@ometrics.com</a>.</li>
                    <li>Or, give us a call at <a href="tel:1.800.700.8077">800.700.8077</a>.</li>
                  </ul>

                  <div id="bot-found" style="<?php echo ($this->settings['ometrics_agent']) ? 'display:block;' : 'display:none;'; ?>"><span id="activate" class="agentStatus" style="display:none;">Your Ochatbot is ready for you to try out, just activate it!</span><span id="deactivate" class="agentStatus" style="display:none;">Your Ochatbot is live!</span> <label class="switch">
    <input type="checkbox" name="agent_status" id="agent_status" />
    <span class="slider round"></span>
  </label>
                    <p>But, for your Ochatbot to perform at it's best, you need to configure it.  <a id="ometrics-configure" href="https://www.ometrics.com/user/redirect/ochatbot-builder?agentId=<?php echo $this->settings['ometrics_agent']; ?>" style="display:none;">Click here to log in and get started</a>.
                    </p>
                    <p>Once the basic configurations are done, you can customize your Ochatbot to your heart's content.  Customize your Ochatbot, view reports, add any of our 9 conversion optimization tools &amp; more by visiting <a href="https://www.ometrics.com/user">your Dashboard at Ometrics.com.</a>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /postbox -->
        </div>
        <!-- /normal-sortables -->
      </div>
      <!-- /post-body-content -->

    </div>
  </div>
</div>
<div id="saveMessage" style="font-size:20px; font-weight:bold;"></div>
