<div class="wrap">
    <h2>RevenueHits Official WP Plugin</h2>

    <div>This plugin is automatically injected the ads, which you choose.</div>

    <form method="post" action="options.php">
        <?php settings_fields('revenuehits_settings'); ?>
        <?php do_settings_sections('revenuehits_settings'); ?>
        <table class="form-table">

            <tr valign="top">
                <th scope="row">Active?</th>
                <td id="front-static-pages">
                    <p>
                        <label><input name="revenuehits_show" type="radio" value="1" class="revenuehits_show"
                                      <?php if (!get_option('revenuehits_show') || get_option('revenuehits_show') == 1): ?>checked="checked"<?php endif; ?>>On</label>
                    </p>
                    <p>
                        <label><input name="revenuehits_show" type="radio" value="2" class="revenuehits_show"
                                      <?php if (get_option('revenuehits_show') == 2): ?>checked="checked"<?php endif; ?>>Off</label>
                    </p>
                </td>
            </tr>

            <tr valign="top" class="revenue-box <?php if (get_option('revenuehits_show') == 2): ?>hide-box<?php endif; ?>">
                <th scope="row">Username</th>
                <td><input type="text" name="revenuehits_userid" value="<?php echo esc_attr(get_option('revenuehits_userid')); ?>"/></td>
            </tr>
            <tr valign="top" class="revenue-box <?php if (get_option('revenuehits_show') == 2): ?>hide-box<?php endif; ?>">
                <th scope="row">Password</th>
                <td>
                    <input type="password" name="revenuehits_password" />
                    <p class="description">Please type your RevenueHits publisher account password</p>
                </td>
            </tr>

            <tr valign="top" class="revenue-box <?php if (get_option('revenuehits_show') == 2): ?>hide-box<?php endif; ?>">
                <th scope="row">Excluded pages</th>
                <td>
                    <textarea id="excluded-pages" class="example" style="width: 400px;" name="revenuehits_exclude_pages" rows="1"></textarea>
                    <span class="error-exclude-page description">Page is already exluded!</span>
                    <p class="description">Start print name of page,on which does not display the code</p>
                </td>
            </tr>

            <tr valign="top" class="revenue-box <?php if (get_option('revenuehits_show') == 2): ?>hide-box<?php endif; ?>">
                <th scope="row">Show in</th>
                <td>
                    <p><label for="revenuehits_homepage">
                            <input name="revenuehits_homepage" type="checkbox" id="revenuehits_homepage"
                                   <?php if (get_option('revenuehits_homepage')): ?>checked="checked"<?php endif; ?>>
                            Home page</label></p>
                    <p><label for="revenuehits_categories">
                            <input name="revenuehits_categories" type="checkbox" id="revenuehits_categories"
                                   <?php if (get_option('revenuehits_categories')): ?>checked="checked"<?php endif; ?>>
                            Categories</label></p>
                    <p><label for="revenuehits_posts">
                            <input name="revenuehits_posts" type="checkbox" id="revenuehits_posts" <?php if (get_option('revenuehits_posts')): ?>checked="checked"<?php endif; ?>>
                            Posts</label></p>
                    <p><label for="revenuehits_other">
                            <input name="revenuehits_other" type="checkbox" id="revenuehits_other" <?php if (get_option('revenuehits_other')): ?>checked="checked"<?php endif; ?>>
                            Other pages</label></p>
                </td>
            </tr>

        </table>
        <h3>Ad Types</h3>
        <table class="form-table" style="width:auto">
            <thead>
            <tr>
                <th>Type</th>
                <th>Activation</th>
                <th>Extra data</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($GLOBALS['RH_ZONE_TYPES'] as $k => $v) { ?>
                <tr valign="top" class="revenue-box <?php if (get_option('revenuehits_show') == 2): ?>hide-box<?php endif; ?>">
                    <th scope="row"><?php echo $v->niceName; ?></th>
                    <td>
                        <p>
                            <label for="position-<?php echo $v->name; ?>">
                                <input name="revenuehits_position_<?php echo $v->name; ?>" type="checkbox" id="position-<?php echo $v->name; ?>"
                                    <?php echo get_option('revenuehits_position_' . $v->name) != 'on' ? '' : 'checked="checked"'; ?>/>
                            </label>
                        </p>

                    </td>
                    <td>
                        <?php
                        switch ($k) {
                            case 'MOBILE_NOTIFIER':
                            case 'MOBILE_DIALOG':
                                ?>
                                <p>
                                    <label for="extra-<?php echo $v->name; ?>">
                                        <span>Text that will appear on the mobile <?php echo $v->name; ?></span><br/>
                                        <input name="revenuehits_extra_<?php echo $v->name; ?>" type="text" id="extra-<?php echo $v->name; ?>"
                                               value="<?php echo get_option('revenuehits_extra_' . $v->name); ?>"/>
                                    </label>
                                </p>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <input type="hidden" name="action" value="update"/>

        <?php submit_button(); ?>
    </form>
</div>
