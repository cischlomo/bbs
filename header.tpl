<!DOCTYPE html>
<head>
<link rel="stylesheet" type="text/css" href="/bbs/style/BLACK.css">
</head>
<body>
<div id="punwrap">
	<div class="pun"> <!-- id="punviewforum/topic"  -->
		<div id="brdheader" class="block">
			<div class="box">
				<div id="brdtitle" class="inbox">
					<a href="#">logo</a>
				</div>
				<div class="clearer"></div>
				<div id="brdmenu" class="inbox">
					<ul>
					{$navlinks=$forum["navlinks"]}
					{section name=navlink loop=$navlinks}
						<li><a href="{$navlinks[navlink]["link_url"]}">{$navlinks[navlink]["link_text"]}</a></li>
					{/section}
					</ul>
				</div>
				<div id="brdwelcome" class="inbox">
						{if $user["is_guest"]}
							Not logged in
						{else}
							<ul class="conl">
							<li>Logged in as {$user["username"]}</li>
						</ul>
					{/if}
					<div class="clearer"></div>
				</div><!-- /brdwelcome -->
			</div>
		</div>
