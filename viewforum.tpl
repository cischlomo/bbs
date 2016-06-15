{include file="header.tpl"}
		<div class="linkst">
			<div class="inbox">
				Pages: {pagination forum=$forum}
				<p class="postlink conr"><a href="/bbs/ui/new_topic/{$forum["id"]}">Post new topic</a></p>
				<ul><li><a href="/bbs/ui/forum/{$forum["id"]}">Index</a>&nbsp;</li><li>»&nbsp;{$forum["forum_name"]}</li></ul>
				<div class="clearer"></div>
			</div>
		</div><!-- /linkst -->
		<div id="vf" class="blocktable">
			<h2><span>{$forum["forum_name"]}</span></h2>
			<div class="box">
				<div class="inbox">
					<table>
						<thead>
							<tr>
								<th class="tcl" scope="col">Topic</th>
								<th class="tc2" scope="col">Replies</th>
								<th class="tc3" scope="col">Views</th>
								<th class="tcr" scope="col">Last&nbsp;post</th>
							</tr>
						</thead>
						<tbody>
							{$topics=$forum["topics"]}
							{section name=topic loop=$topics}
							<tr>
								<td class="tcl">
									<div class="intd">
										<div class="icon"><div class="nosize"><!-- --></div></div>
										<div class="tclcon">
											<a href="/bbs/ui/topic/{$forum["topics"][topic]["id"]}">{$topics[topic]["subject"]}</a>
											<span class="byuser">by&nbsp;{$topics[topic]["poster"]}</span>
										</div>
									</div>
								</td>
								<td class="tc2">{$topics[topic]["num_replies"]}</td>
								<td class="tc3">{$topics[topic]["num_views"]}</td>
								<td class="tcr"><a href="/bbs/ui/post/{$topics[topic]["last_post_id"]}">
								{format_time time=$topics[topic]['last_post']}</a> <span class="byuser">by&nbsp;{$topics[topic]["last_poster"]}</span></td>
							</tr>
							{/section}
							</tbody>
					</table>
				</div><!-- /inbox -->
			</div><!-- /box -->
		</div><!-- /blocktable -->
		<div class="linksb">
			<div class="inbox">
				<p class="pagelink conl">Pages: {pagination forum=$forum}</p>
				<p class="postlink conr"><a href="/bbs/ui/new_topic/{$forum["id"]}">Post new topic</a></p>
				<ul></ul>
				<div class="clearer"></div>
			</div>
		</div>
	</div><!-- /punviewforum -->
</div><!-- /punwrap -->
</body>
</html>
