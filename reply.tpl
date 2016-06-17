{include file="header.tpl"}
{$forum=$post["forum"]}
{$topic=$post["topic"]}
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<p class="postlink conr"><?php echo $post_link ?></p>
		<ul><li><a href="/bbs/ui/">Index</a></li><li>&nbsp;&raquo;&nbsp;<a href="/bbs/ui/forum/{$forum['id']}">{$forum['forum_name']}</a></li><li>&nbsp;&raquo;&nbsp;
		{$topic['subject']}</li></ul>
		<div class="clearer"></div>
	</div>
</div>
