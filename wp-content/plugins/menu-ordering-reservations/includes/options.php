<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (isset($_GET['reset']) && $_GET['reset'] == 'true') {
    $this->remove_user_data();
    ?>
    <script>window.location.href = 'options-general.php?page=glf-options'</script>
    <?php
}
  ?>
<div class="wrap">
    <h1><?php _e('Menu - Ordering - Reservations Settings', 'menu-ordering-reservations') ?></h1>
    <?php if ($this->error) {?>
    <div class="error notice">
        <p><?= $this->error->get_error_message();?></p>
    </div>
    <?php } ?>
    <?php if ($this->is_authenticated()) {?>
            <h2><?php _e('Disconnect Account', 'menu-ordering-reservations') ?></h2>
            <div>
                <div>
                    <p><?php _e('If you disconnect your account, the <i>See Menu & Order</i> and <i>Table Reservation</i> buttons that are already published on your pages will remain as they are. However, you will not longer be able to make changes to your restaurant profile from within the WordPress interface.', 'menu-ordering-reservations');?></p>
                </div>
                <table class="form-table">
                    <tr valign="top">
                        <td><?php _e('Email', 'menu-ordering-reservations') ?></td>
                        <td><code><?= $this->user->email ?></code></td>
                    </tr>
                    <tr valign="top">
                        <td><?php _e('Installation id', 'menu-ordering-reservations') ?></td>
                        <td><code><?= $this->installation_id ?></code></td>
                    </tr>
                    <tr valign="top">
                        <td></td>
                        <td>
                            <a onclick="if(!confirm('<?php _e('Are you sure you want to disconnect your account?', 'menu-ordering-reservations');?>')) return false;" href="options-general.php?page=glf-options&reset=true">
                                <button class="button button-primary"><?php _e('Disconnect Account', 'menu-ordering-reservations') ?></button>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        <?php } else {?>
            <h2><?php _e('Connect Account', 'menu-ordering-reservations') ?></h2>
            <div>
                <p><?php _e("Please connect a restaurant account to get started", 'menu-ordering-reservations');?></p>
                <p><a href="<?php menu_page_url('glf-admin', true);?>" class="button button-primary"><?php _e('Connect Account', 'menu-ordering-reservations') ?></a></p>
             </div>
        <?php }?>
    </div>
