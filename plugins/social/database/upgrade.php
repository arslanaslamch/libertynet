<?php
function social_upgrade_database() {
	add_email_template("social-invite-member", array(
		'title' => 'Social Invite Members',
		'description' => 'This is the email sent to invited social accounts like gmail , facebook imports',
		'subject' => '[inviter] invited you to [site-title]',
		'body_content' => '[header]
<div style="width: 500px; margin: auto; padding: 16px; font-family: sans-serif;">
    <h1 style="text-align: center;">Password reset</h1>
    <p>[inviter] invited you to [site-title]. Please follow this link below to register.</p>
    <p style="text-align: center"><a href="[link]" style="margin: 8px 0px; border-radius: 7px; background-color: #555555; padding: 16px; color: #FFFFFF; text-decoration: none; display: inline-block;">Register</a></p>
    <p style="text-align: center"><a href="[link]" style="color: #555555;">[link]</a></p>
</div>
[footer]',
		'placeholders' => '[link],[reg-link],[inviter][inviter-link],[inviter-avatar]'
	));
}