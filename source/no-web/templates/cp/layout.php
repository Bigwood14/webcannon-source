<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php if (empty($template->page_title)) { ?>.:: Webcannon ::.<?php } else { echo $template->page_title; } ?></title>
<link href="/css/old.css" rel="stylesheet" type="text/css" media="all" />
<link href="/css/main.css" rel="stylesheet" type="text/css" media="all" />
<link href="/css/clearfix.css" rel="stylesheet" type="text/css" media="all" />
<!--<link href="/css/menu.css" rel="stylesheet" type="text/css" media="all" />-->

<link rel="stylesheet" type="text/css" href="/js/yui/menu/assets/skins/sam/menu.css"> 

<?php if (!empty($template->styles)) {
    foreach ($template->styles as $stylesheet) { ?>
<link rel="stylesheet" type="text/css" href="/css/<?php echo $stylesheet; ?>" />
<?php }
} ?>
<script type="text/JavaScript" src="/js/functions.js"></script>
<script type="text/JavaScript" src="/js/jquery.js"></script>
<script type="text/JavaScript" src="/js/shared.js"></script>

<?php if (!empty($template->scripts)) {
    foreach ($template->scripts as $js) { ?>
<script type="text/javascript" src="/js/<?php echo $js; ?>"></script>
<?php }
} ?>
</head>

<body>
<div align="center">
  <table width="931" border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td background="/images/top_red.gif"><img src="/images/spacer.gif" width="2" height="7" /></td>
    </tr>
    <tr>
      <td background="/images/top_bg.gif">
	  	<div align="left">
			<a href="/cp/"><img src="/images/logo.gif" alt="Webcannon Logo" width="308" height="82" id="logo" /></a>
			<span id="version">4.2.3</span></div>
	  </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><table width="94%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>

            <td width="193" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td><img src="/images/login_left.gif" width="13" height="28" /></td>
                        <td><img src="/images/login_middle.gif" width="167" height="28" /></td>
                        <td><img src="/images/login_right.gif" width="13" height="28" /></td>
                      </tr>
                  </table></td>

                </tr>
                <tr>
                  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td width="10" bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td width="182" bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td width="10" bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                      </tr>
                      <tr>

                        <td width="1" bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td>
<?php
global $permissions;

$menu 					= array();
$menu['Scheduling'] 	= array();
$menu['Scheduling'][] 	= array('href' => '/cp/scheduling/delivery-queue.php', 'title' => 'Delivery Queue');
$menu['Scheduling'][] 	= array('href' => '/cp/scheduling/draft.php', 'title' => 'New Draft');
$menu['Scheduling'][] 	= array('href' => '/cp/scheduling/draft-archive.php', 'title' => 'Draft Archive');

$menu['List Management']	= array();
$menu['List Management'][] 	= array('href' => '/cp/management/lists.php', 'title' => 'Lists');
$menu['List Management'][] 	= array('href' => '/cp/management/supression-lists.php', 'title' => 'Suppression Lists');
$menu['List Management'][] 	= array('href' => '/cp/management/import-wizard.php', 'title' => 'Create Import');
$menu['List Management'][] 	= array('href' => '/cp/management/imports.php', 'title' => 'Imports');
if ($permissions->auth->user['mailer'] != 1)
	$menu['List Management'][] 	= array('href' => '/cp/management/export.php', 'title' => 'Exports');
if ($permissions->auth->user['mailer'] != 1)
	$menu['List Management'][] 	= array('href' => '/cp/management/recipient-add.php', 'title' => 'Add Recipient');
if ($permissions->auth->user['mailer'] != 1)
	$menu['List Management'][] 	= array('href' => '/cp/management/recipient-find.php', 'title' => 'Find Recipient');
$menu['List Management'][] 	= array('href' => '/cp/management/recipient-remove.php', 'title' => 'Remove Recipient');

$menu['Whitelisting'] 		= array();
$menu['Whitelisting'][] 	= array('href' => '/cp/isp-relations/aol_fb_domain.php', 'title' => 'Feedback Loop');
$menu['Whitelisting'][] 	= array('href' => '/cp/isp-relations/aol_domain.php', 'title' => 'Track Whitelist');
$menu['Whitelisting'][] 	= array('href' => '/cp/isp-relations/aol_removal.php', 'title' => 'Removal Assistance');
$menu['Whitelisting'][] 	= array('href' => '/cp/isp-relations/aol_complaint.php', 'title' => 'Complaints');

$menu['Config'] 	= array();
$menu['Config'][] 	= array('href' => '/cp/options/general_config.php', 'title' => 'Config');

if ($permissions->auth->user['mailer'] != 1)
	$menu['Config'][] 	= array('href' => '/cp/options/delivery_configuration.php', 'title' => 'Delivery Configuration');

$menu['Config'][] 	= array('href' => '/cp/options/header-footer.php', 'title' => 'Header/Footers');
$menu['Config'][] 	= array('href' => '/cp/options/password-change.php', 'title' => 'Change Password');
if ($permissions->auth->user['mailer'] != 1)
	$menu['Config'][] 	= array('href' => '/cp/isp-relations/domains.php', 'title' => 'Domain Management');
if ($permissions->auth->user['mailer'] != 1)
	$menu['Config'][] 	= array('href' => '/cp/options/pages.php', 'title' => 'Pages');
$menu['Config'][] 	= array('href' => '/cp/isp-relations/seed.php', 'title' => 'Seed Accounts');
if ($permissions->auth->user['mailer'] != 1)
	$menu['Config'][] 	= array('href' => '/cp/options/user.php', 'title' => 'User Management');
?>	
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td><img src="/images/main_menu.gif" width="191" height="31" /></td>
                            </tr>
                            <tr>
                              <td><div align="left">
                                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php foreach ($menu as $title => $item) { ?>
	<tr>
		<td width="6%"><img src="/images/spacer.gif" width="12" height="28" /></td>
		<td width="6%"><img src="/images/bullet_01.gif" width="5" height="7" /></td>
		<td width="88%" class="ver11bold"><?php echo $title ?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>
			<div align="right">
				<table width="100%"  border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
					</tr>
					<tr>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
						<td>
							<table width="100%"  border="0" cellpadding="0" cellspacing="0" class="ver11bold">
								<?php foreach ($item as $link) { ?>
								<tr>
									<td height="20" bgcolor="#FBFBFB">
										<div align="left">
											&nbsp;&nbsp;&nbsp;
											<?php if ($_SERVER['SCRIPT_NAME'] == $link['href']) { ?>
												<u><a href="<?php echo $link['href'] ?>"><?php echo $link['title'] ?></a></u>
											<?php } else { ?>
												<a href="<?php echo $link['href'] ?>"><?php echo $link['title'] ?></a>
											<?php } ?>
										</div>
									</td>
								</tr>
								<tr>
									<td bgcolor="#E9E9E9"><img src="/images/spacer.gif" width="1" height="1" /></td>
								</tr>
								<?php } ?>
							</table>
						</td>
						<td><img src="/images/spacer.gif" width="1" height="1" /></td>
					</tr>
					<tr>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
						<td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
					</tr>
				</table>
				<br />
			</div>
		</td>
	</tr>
<?php } ?>
                                      </td>

                                    </tr>
                                  </table>
                              </div></td>
                            </tr>
                          </table>
                            <p>&nbsp;</p>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p></td>
                        <td width="1" bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>

                      </tr>
                      <tr>
                        <td width="10" bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td bgcolor="#D4D4D4"><img src="/images/spacer.gif" width="1" height="1" /></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>

                  <td>&nbsp;</td>
                </tr>
            </table></td>
            <td width="30">&nbsp;</td>
            <td valign="top"><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td width="15%"><a href="/cp"><img src="/images/home_page.gif" width="99" height="31" border="0" /></a></td>

                        <td width="85%"><div align="left"><a href="/wbl.php"><img src="/images/logout.gif" width="99" height="31" border="0" /></a></div></td>
                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>

                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
                      </tr>
                      <tr>
                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td height="30" bgcolor="#F7F7F7" class="ver11normal"><div align="left">
                            <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                              <tr>
                                <td width="2%"><img src="/images/spacer.gif" width="15" height="15" /></td>
                                <td width="98%">Logged in as : <?php echo $template->layout_username; ?> - Server Time: <?php echo strftime("%d-%b-%y %H:%M:%S"); ?></td>

                              </tr>
                            </table>
                        </div></td>
                        <td width="1" bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /><img src="/images/spacer.gif" width="1" height="1" /></td>
                      </tr>
                      <tr>
                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>
                        <td bgcolor="#E6E6E6"><img src="/images/spacer.gif" width="1" height="1" /></td>

                      </tr>
                  </table></td>
                </tr>
                <tr>
                  <td height="6"><img src="/images/spacer.gif" width="1" height="1" /></td>
                </tr>
                <tr>
                    <td class="ver11normal">
                    <?php if (!empty($template->errors)) { ?>
                    <ul id="errors">
                        <?php foreach ($template->errors as $error) { ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php } ?>
                    </ul>
                    <?php } ?>

                    <?php if (!empty($template->msgs)) { ?>
                    <ul id="messages">
                        <?php foreach ($template->msgs as $msg) { ?>
                        <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php } ?>
                    </ul>
                    <?php } ?>

                    <?php require $template->directory . $template->template; ?>
                    </td>
                </tr>
            </table></td>
          </tr>
      </table></td>
    </tr>
    <tr>

      <td>&nbsp;</td>
    </tr>
    <tr>
      <td bgcolor="#CECDCD"><img src="/images/spacer.gif" width="1" height="1" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
    </tr>
  </table>

</div>
</body>
</html>

