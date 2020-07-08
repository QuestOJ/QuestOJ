<?php
	requirePHPLib('form');
	
	if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	if (!hasProblemPermission(Auth::user(), $problem)) {
		become403Page();
	}
	
	$managers_form = newAddAuthCmdForm('managers',
		function($op, $groupname) {
			if ($op == 'A' && $groupname == 'LL'){
				return '';
			}
			else if($op == 'C' && $groupname == 'LEAR'){
				return '';
			}
			else if(!validateString($groupname) || !queryGroup($groupname)) {
				return "不存在名为{$groupname}的用户组";
			}
			return '';
		},
		function($type, $groupname) {
			global $problem;
			
			if($type == 'A'){
				DB::update("update problems set is_all = 1 where id = ${problem['id']}");
			}else if($type == 'C'){
				DB::delete("delete from problems_auth where pid = ${problem['id']}");
				DB::update("update problems set is_all = 0 where id = ${problem['id']}");
			}
			
			foreach (queryGroup($groupname) as $group){
				$id = $group['id'];
				if ($type == '+') {
					DB::insert("insert into problems_auth (pid, gid) values (${problem['id']}, '$id')");
					DB::update("update problems set is_all = 0 where id = ${problem['id']}");
				} else if ($type == '-') {
					DB::delete("delete from problems_auth where pid = ${problem['id']} and gid = '$id'");
				}
			}
		}
	);
	
	$managers_form->runAtServer();
?>
<?php echoUOJPageHeader(HTML::stripTags($problem['title']) . ' - 查看权限 - 题目管理') ?>
<h1 class="page-header" align="center">#<?=$problem['id']?> : <?=$problem['title']?> 管理</h1>
<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item"><a class="nav-link" href="/problem/<?= $problem['id'] ?>/manage/statement" role="tab">编辑</a></li>
	<li class="nav-item"><a class="nav-link" href="/problem/<?= $problem['id'] ?>/manage/managers" role="tab">管理者</a></li>
	<li class="nav-item"><a class="nav-link active" href="/problem/<?= $problem['id'] ?>/manage/auth" role="tab">查看权限</a></li>		
	<li class="nav-item"><a class="nav-link" href="/problem/<?= $problem['id'] ?>/manage/data" role="tab">数据</a></li>
	<li class="nav-item"><a class="nav-link" href="/problem/<?=$problem['id']?>" role="tab">返回</a></li>
</ul>

<table class="table table-hover">
	<thead>
		<tr>
			<th width='30%'>#</th>
			<th>组别</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$problem_allow = DB::selectAll("select * from problems where id = {$_GET['id']} ");
			foreach ($problem_allow as $problem){
				$all = $problem['is_all'];
				if($all == 1){
					echo "<tr>";
					echo "<td>*</td>";
					echo "<td>ALL GROUP</td>";
					echo "</tr>";
					break;
				}
				
				$group_allow = DB::selectAll("select * from problems_auth where pid = {$_GET['id']} ");
				foreach ($group_allow as $group){
					$gid = $group['gid'];
					
					$group_info = DB::selectAll("select * from usergroup where id = {$gid} ");
					
					foreach ($group_info as $info){
						echo "<tr>";
						echo "<td>{$gid}</td>";
						echo "<td>{$info['name']}</td>";
						echo "</tr>";
					}
				}
			}
		?>
	</tbody>
</table>
<p class="text-center">命令格式：命令一行一个，+mike表示允许mike组查看，-mike表示禁止mike组查看，ALL表示不作限制，CLEAR表示清空</p>
<?php $managers_form->printHTML(); ?>
<?php echoUOJPageFooter() ?>

