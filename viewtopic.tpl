{include file="header.tpl"}
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<p class="postlink conr"><?php echo $post_link ?></p>
		<ul><li><a href="index.php">Index</a></li><li>&nbsp;&raquo;&nbsp;<a href="/bbs/ui/forum/{$forum['id']}">{$forum['forum_name']}</a></li><li>&nbsp;&raquo;&nbsp;
		{$topic['subject']}</li></ul>
		<div class="clearer"></div>
	</div>
</div>
{$posts=$topic["posts"]}
{section name=post loop=$posts}
<div id="p{$posts[post]['id']}" class="blockpost rowodd firstpost">
	<h2><span><span class="conr">#1&nbsp;</span><a href="/bbs/ui/topic/{$posts[post]['id']}">Yesterday 21:25:00</a></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postleft">
				<dl>
					<dt><strong></strong></dt>
					<dd class="usertitle"><strong>.</strong></dd>
					<dd class="postavatar"></dd>
				</dl>
			</div>
			<div class="postright">
				<h3>{$topic['subject']}</h3>
				<div class="postmsg">
					<p>{$posts[post]["message"]}</p>
				</div>
			</div>
			<div class="clearer"></div>
			<div class="postfootleft"></div>
			<div class="postfootright"><ul><li class="postquote"><a href="/bbs/ui/reply/{$posts[post]["pid"]}">Quote</a></li></ul></div>
		</div>
	</div>
</div>
{/section}
<div class="postlinksb">
	<div class="inbox">
		<p class="postlink conr"><?php echo $post_link ?></p>
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<ul><li><a href="index.php">Index</a></li><li>&nbsp;&raquo;&nbsp;<a href="/bbs/ui/forum/{$forum['id']}">{$forum['forum_name']}</a></li><li>&nbsp;&raquo;&nbsp;{$topic['subject']}</li></ul>
		 
	</div>
</div>

</body>
</html>