<div class="wrap">

    <?php include_once(JRP::PATH . '/view/_header.php'); ?>

    <div id="poststuff" class="metabox-holder">

        <?php include_once(JRP::PATH . '/view/_messages.php'); ?>

        <div id="post-body" class="has-sidebar">

            <div id="post-body-content" class="has-sidebar-content">

                <div id='normal-sortables' class='meta-box-sortables'>

                    <div class="postbox " >
                        <h3 class='hndle' style='cursor:default;'>
                            <span style='vertical-align: top;'>Jobroller Feed Settings</span>
                        </h3>
                        <div class="inside">
                            <div class="notes">
                                <p>Registered RSS feeds:</p>
                                <ul>
                                    <?php if (JRP::FEED_GLASSDOOR): ?>
                                    <li>
                                        <code><?php echo site_url('rss/glassdoor'); ?></code> - <a href="<?php echo site_url('rss/glassdoor'); ?>" target="_blank">Feed</a> for <a href="http://www.glassdoor.com/" target="_blank">Glassdoor</a> job search service.
                                    </li>
                                    <?php endif; ?>
                                    <?php if (JRP::FEED_SIMPLYHIRED): ?>
                                    <li>
                                        <code><?php echo site_url('rss/simplyhired'); ?></code> - <a href="<?php echo site_url('rss/simplyhired'); ?>" target="_blank">Feed</a> for <a href="http://www.simplyhired.com/" target="_blank">Simply Hired</a> job search service.
                                    </li>
                                    <?php endif; ?>
                                    <?php if (JRP::FEED_JUJU): ?>
                                    <li>
                                        <code><?php echo site_url('rss/juju'); ?></code> - <a href="<?php echo site_url('rss/juju'); ?>" target="_blank">Feed</a> for <a href="http://www.job-search-engine.com/" target="_blank">Juju</a> job search service.
                                    </li>
                                    <?php endif; ?>
                                    <?php if (JRP::FEED_TWITJOBSEARCH): ?>
                                    <li>
                                        <code><?php echo site_url('rss/twitterjs'); ?></code> - <a href="<?php echo site_url('rss/twitterjs'); ?>" target="_blank">Feed</a> for <a href="http://www.twitjobsearch.com/" target="_blank">TwitJobSearch</a> job search engine service.
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <?php if (JRP::FEED_GLASSDOOR): ?>
                                <div class="feed-settings">
                                    <p class="setting-title">Glassdoor Settings</p>
                                    <table>
                                        <colgroup>
                                            <col width="15%" />
                                            <col width="100%" />
                                        </colgroup>
                                        <tbody>
                                            <tr><td>Enabled:</td><td><input type="radio" name="feed_settings[glassdoor][enabled]" value="1" <?php echo $feed_settings['glassdoor']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;<input type="radio" name="feed_settings[glassdoor][enabled]" value="0" <?php echo !$feed_settings['glassdoor']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;No</td></td></tr>
                                            <tr><td>Publisher:</td><td><input type="text" name="feed_settings[glassdoor][publisher]" value="<?php echo $feed_settings['glassdoor']['publisher']; ?>" style="width:50%"></td></tr>
                                            <tr><td>Publisher URL:</td><td><input type="text" name="feed_settings[glassdoor][publisherurl]" value="<?php echo $feed_settings['glassdoor']['publisherurl']; ?>" style="width:50%"></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if (JRP::FEED_SIMPLYHIRED): ?>
                                <div class="feed-settings">
                                    <p class="setting-title">Simply Hired Settings</p>
                                    <table>
                                        <colgroup>
                                            <col width="15%" />
                                            <col width="100%" />
                                        </colgroup>
                                        <tbody>
                                            <tr><td>Enabled:</td><td><input type="radio" name="feed_settings[simplyhired][enabled]" value="1" <?php echo $feed_settings['simplyhired']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;<input type="radio" name="feed_settings[simplyhired][enabled]" value="0" <?php echo !$feed_settings['simplyhired']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;No</td></td></tr>
                                            <tr><td>Source:</td><td><input type="text" name="feed_settings[simplyhired][source]" value="<?php echo $feed_settings['simplyhired']['source']; ?>" style="width:50%"></td></tr>
                                            <tr><td>Source URL:</td><td><input type="text" name="feed_settings[simplyhired][sourceurl]" value="<?php echo $feed_settings['simplyhired']['sourceurl']; ?>" style="width:50%"></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if (JRP::FEED_JUJU): ?>
                                <div class="feed-settings">
                                    <p class="setting-title">Juju Settings</p>
                                    <table>
                                        <colgroup>
                                            <col width="15%" />
                                            <col width="100%" />
                                        </colgroup>
                                        <tbody>
                                            <tr><td>Enabled:</td><td><input type="radio" name="feed_settings[juju][enabled]" value="1" <?php echo $feed_settings['juju']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;<input type="radio" name="feed_settings[juju][enabled]" value="0" <?php echo !$feed_settings['juju']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;No</td></td></tr>
                                            <tr><td>Source:</td><td><input type="text" name="feed_settings[juju][source]" value="<?php echo $feed_settings['juju']['source']; ?>" style="width:50%"></td></tr>
                                            <tr><td>Source URL:</td><td><input type="text" name="feed_settings[juju][sourceurl]" value="<?php echo $feed_settings['juju']['sourceurl']; ?>" style="width:50%"></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if (JRP::FEED_TWITJOBSEARCH): ?>
                                <div class="feed-settings">
                                    <p class="setting-title">TwitJobSearch Settings</p>
                                    <table>
                                        <colgroup>
                                            <col width="15%" />
                                            <col width="100%" />
                                        </colgroup>
                                        <tbody>
                                            <tr><td>Enabled:</td><td><input type="radio" name="feed_settings[twitjobsearch][enabled]" value="1" <?php echo $feed_settings['twitjobsearch']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;<input type="radio" name="feed_settings[twitjobsearch][enabled]" value="0" <?php echo !$feed_settings['twitjobsearch']['enabled'] ? 'checked="checked"' : ''; ?>>&nbsp;No</td></td></tr>
                                            <tr><td>Publisher:</td><td><input type="text" name="feed_settings[twitjobsearch][publisher]" value="<?php echo $feed_settings['twitjobsearch']['publisher']; ?>" style="width:50%"></td></tr>
                                            <tr><td>Publisher URL:</td><td><input type="text" name="feed_settings[twitjobsearch][publisherurl]" value="<?php echo $feed_settings['twitjobsearch']['publisherurl']; ?>" style="width:50%"></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <table style="width:100%">
                                    <tbody>
                                        <tr><td colspan="2" style="text-align:right;"><input type="submit" class="button" name="save_feed_settings" value="Save"></td></tr>
                                    </tbody>
                                </table>
                            </form>
                         </div>
                    </div>


                    <div class="postbox " >
                        <h3 class='hndle' style='cursor:default;'>
                            <span style='vertical-align: top;'>Jobroller Share Settings</span>
                        </h3>
                        <div class="inside">
                            <div class="notes">
                                <p>Additional share options (share buttons in Live Jobs page, etc.).</p>
                                <p>JRPlus can use bit.ly URL shortening service by utilizing <a href="http://wordpress.org/plugins/wp-bitly/" taget="_blank">WP Bit.ly</a> plugin.</p>
                                <p>JRPlus can tweet directly to your Twitter account when new job is approved. In order to utilize this feature just install <a href="http://wordpress.org/plugins/wp-to-twitter/" taget="_blank">WP to Twitter</a> plugin and follow it's setup instructions.</p>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <table style="width:100%">
                                    <colgroup>
                                        <col width="25%" />
                                        <col width="100%" />
                                    </colgroup>
                                    <tbody>
                                        <tr><td>Show share buttons in Live Jobs table:</td><td><input type="radio" name="share_settings[enable_share_buttons_in_live_jobs_table]" value="1" <?php echo $share_settings['enable_share_buttons_in_live_jobs_table'] ? 'checked="checked"' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;<input type="radio" name="share_settings[enable_share_buttons_in_live_jobs_table]" value="0" <?php echo !$share_settings['enable_share_buttons_in_live_jobs_table'] ? 'checked="checked"' : ''; ?>>&nbsp;No</td></tr>
                                        <tr><td>Default share image:</td><td><input type="hidden" name="share_settings[default_share_image]" value="<?php echo $share_settings['default_share_image']; ?>"><input type="button" class="button choose-default-share-image" value="Choose image"></td></tr>
                                        <tr><td></td><td>
                                            <div id="default-share-image">
                                                <?php if ($share_settings['default_share_image'] != ''): ?>
                                                <img src="<?php echo $share_settings['default_share_image']; ?>">
                                                <?php endif; ?>
                                            </div>
                                        </td></tr>
                                    </tbody>
                                </table>
                                <table style="width:100%">
                                    <tbody>
                                        <tr><td colspan="2" style="text-align:right;"><input type="submit" class="button" name="save_share_settings" value="Save"></td></tr>
                                    </tbody>
                                </table>
                            </form>
                         </div>
                    </div>

                    <div class="postbox " >
                        <h3 class='hndle' style='cursor:default;'>
                            <span style='vertical-align: top;'>Jobroller API Settings</span>
                        </h3>
                        <div class="inside">
                            <div class="notes">
                                <p>API endpoint: <code><?php echo site_url('api'); ?></code></p>
                            </div>
                                <?php echo $api_page->post_content; ?>
                         </div>
                    </div>

                </div>

            </div> <!-- class="has-sidebar-content" -->

        </div> <!-- class="has-sidebar" -->

    </div> <!-- class="metabox-holder" -->

</div> <!-- class="wrap" -->

<?php include_once(JRP::PATH . '/view/_footer.php'); ?>