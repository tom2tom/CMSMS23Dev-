<!doctype html>
<html lang="{$lang_code|truncate:'2':''}" dir="{$lang_dir|default:'ltr'}">
	<head>
		<title>{'logintitle'|lang} - {sitename}</title>
		<meta charset="{$encoding}" />
		<meta name="generator" content="CMS Made Simple" />
		<meta name="robots" content="noindex, nofollow" />
		<meta name="viewport" content="initial-scale=1.0 maximum-scale=1.0 user-scalable=no" />
		<meta name="HandheldFriendly" content="true" />
		<meta name="msapplication-TileColor" content="#f89938" />
		<meta name="msapplication-TileImage" content="{$admin_url}/themes/Altbier/images/favicon/ms-application-icon.png" />
		<base href="{$admin_url}/" />
		<link rel="shortcut icon" href="themes/Altbier/images/favicon/cmsms-favicon.ico" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,600,600i" />
		<link rel="stylesheet" href="themes/Altbier/css/bootstrap_reboot-grid.min.css" />
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
		{$header_includes|default:''}
	</head>
	<body id="login">
		<div class="container py-5">
			<div class="logo row">
				<div class="col-12 mx-auto text-center"><img class="img-fluid" src="{$admin_url}/themes/Altbier/images/layout/cmsms_login_logo.png" width="310" height="85" alt="CMS Made Simple&trade;" /></div>
			</div>
			<div class="row">
				<div class="mx-auto">
					<div class="login-box p-2 p-sm-4"{if isset($error)} id="error"{/if}>
						<div class="col-12 info-wrapper open">
							<aside class="p-4 info">
								<h2>{'login_info_title'|lang}</h2>
								<p>{'login_info'|lang}</p>
								{'login_info_params'|lang}
								<p class="pl-4"><strong>({$smarty.server.HTTP_HOST})</strong></p>
								<div class="warning-message mt-3 py-3 row">
									<div class="col-2"><i aria-hidden="true" class="fas fa-2x fa-exclamation-circle"></i> </div>
									<p class="col-10">{'warn_admin_ipandcookies'|lang}</p>
								</div>
							</aside>
						</div>
						<header class="col-12 text-center">
							<h1><a href="#" title="{'open'|lang}/{'close'|lang}" class="toggle-info"><span tabindex="0" role="note" aria-label="{'login_info_title'|lang}" class="fas fa-info-circle"></span><span class="sr-only">{'open'|lang}/{'close'|lang}</span></a> {'logintitle'|lang}</h1>
						</header>
						<div class="col-12 mx-auto text-center">
						{if isset($form)}{$form}{else}{include file='form.tpl'}{block name=form}{/block}{/if}
						</div>
						{if !empty($smarty.get.forgotpw)}
							<div tabindex="0" role="alertdialog" class="col-12 message warning mt-2 py-2">
								{'forgotpwprompt'|lang}
							</div>
						{/if}
						{if !empty($error)}
							<div tabindex="0" role="alertdialog" class="col-12 message error mt-2 py-2">
								{$error}
							</div>
						{/if}
						{if !empty($warning)}
							<div tabindex="0" role="alertdialog" class="col-12 message warning mt-2 py-2">
								{$warning}
							</div>
						{/if}
						{if !empty($message)}
							<div tabindex="0" role="alertdialog" class="col-12 message success mt-2 py-2">
								{$message}
							</div>
						{/if}
						{if !empty($changepwhash)}
							<div tabindex="0" role="alertdialog" class="col-12 warning message mt-2 py-2">
								{'passwordchange'|lang}
							</div>
						{/if}
						<div class="col-12 mt-5 px-0">
							<div class="row alt-actions">
								<a class="col-12 col-sm-6" href="{root_url}" title="{'goto'|lang} {sitename}"><span aria-hidden="true" class="fas fa-chevron-circle-left"></span> {'viewsite'|lang}</a>
								<a href="login.php?forgotpw=1" title="{'recover_start'|lang}" class="col-12 text-left text-sm-right col-sm-6"><span class="fas fa-question-circle" aria-hidden="true"></span> {'lostpw'|lang}</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<footer class="row">
				<small class="col-12 copyright">Copyright &copy; <a rel="external" href="http://www.cmsmadesimple.org">CMS Made Simple&trade;</a></small>
			</footer>
		</div>
		{$bottom_includes|default:''}
	</body>
</html>