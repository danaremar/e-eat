<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="wrapper iframe-wrap">
    <div class="white-lp-wrap iframe-lp">
        <div class="title-section title-section-white">
            <div class="container">
                <div class="row title-row">
                    <div class="col-10 col-offset-1 text-center">
                        <h1> <?= __('Explore all the capabilities of this powerful<br> ordering system', 'menu-ordering-reservations');?> </h1>
                        <a class="btn-green btn js-autologin-admin" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.setup' : "admin.php?page=glf-admin"; ?>" target="_blank"> <?= __('Open the Full Admin Panel', 'menu-ordering-reservations');?> </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="section left-img first-section">
            <div class="container">
                <div class="feature-cards">
                    <div class="row">
                        <div class="feature">
                            <div class="feature-icon"> <i class="icon-reports"></i> </div>
                            <div class="feature-description">
                                <h3><?= __('Ordering Reports', 'menu-ordering-reservations');?> <span><?= __('Free', 'menu-ordering-reservations');?></span></h3>
                                <p> <?= __('Reports about orders and clients in various presentation forms: charts, tables and even csv exports.', 'menu-ordering-reservations');?> </p>
                                <a class="js-autologin-reports" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.reports.essentials.analytics_overview' : "admin.php?page=glf-admin";?>" target="_blank"> <?= __('Access the reports', 'menu-ordering-reservations');?> </a>
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon"> <i class="icon-website-ordering"></i> </div>
                            <div class="feature-description">
                                <h3><?= __('3rd Party Tracking', 'menu-ordering-reservations');?> <span><?= __('Free', 'menu-ordering-reservations');?></span></h3>
                                <p> <?= __('Set up Google Tag Manager in the ordering widget and you’re in full control over the conversion tracking & analytics codes you use.', 'menu-ordering-reservations');?> </p>
                                <a class="js-autologin-tracking" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.other.integrations.gtm' : "admin.php?page=glf-admin";?>" target="_blank"> <?= __('Set up tracking', 'menu-ordering-reservations');?> </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="feature">
                            <div class="feature-icon"> <i class="icon-credit-card"></i> </div>
                            <div class="feature-description">
                                <h3><?= __('Online Payments', 'menu-ordering-reservations');?> <span><?= __('Premium', 'menu-ordering-reservations');?></span></h3>
                                <p> <?= __('Connects the ordering system with the restaurant payment gateway. This way, the money go from the food client straight to the restaurant account.', 'menu-ordering-reservations');?> </p>
                                <a class="js-autologin-online-payments" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.setup.online.services.index' : "admin.php?page=glf-admin" ;?>" target="_blank"> <?= __('Enable online payments', 'menu-ordering-reservations');?> </a>
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon"> <i class="icon-advanced-promotions"></i> </div>
                            <div class="feature-description">
                                <h3><?= __('Promotions and Coupons', 'menu-ordering-reservations');?> <span><?= __('Premium', 'menu-ordering-reservations');?></span></h3>
                                <p> <?= __('You can set up almost any promotion logic you can think of (from simple discounts to meal bundles), and trigger them to different customer segments.', 'menu-ordering-reservations');?> </p>
                                <a class="js-autologin-promotions" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.marketing.promotions.overview' : "admin.php?page=glf-admin" ;?>" target="_blank"> <?= __('Access the promotions', 'menu-ordering-reservations');?> </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="feature">
                            <div class="feature-icon"> <i class="icon-facebook-ordering"></i> </div>
                            <div class="feature-description">
                                <h3><?= __('Facebook Ordering', 'menu-ordering-reservations');?> <span><?= __('Free', 'menu-ordering-reservations');?></span></h3>
                                <p> <?= __('This free app adds the ordering button to the restaurant Facebook page, so that people can see the menu and order directly from Facebook.', 'menu-ordering-reservations');?> </p>
                                <a class="js-autologin-fb-ordering" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.setup.publishing.facebook_installation' : "admin.php?page=glf-admin";?>" target="_blank"> <?= __('Install the App', 'menu-ordering-reservations');?> </a>
                            </div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon"> <i class="icon-mobile-check"></i> </div>
                            <div class="feature-description">
                                <h3><?= __('Branded Mobile Apps', 'menu-ordering-reservations');?> <span><?= __('Premium', 'menu-ordering-reservations');?></span></h3>
                                <p> <?= __('Create branded mobile apps for Android and iOS without writing a line of code. Custom logo, slogan and background image.', 'menu-ordering-reservations');?> </p>
                                <a class="js-autologin-apps" href="<?= ($this->get_glf_mor_token()) ? $this->get_glf_mor_token().'&r=app.admin.setup.publishing.wla.info' : "admin.php?page=glf-admin" ;?>" target="_blank"> <?= __('Get started', 'menu-ordering-reservations');?> </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

