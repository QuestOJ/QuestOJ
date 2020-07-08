<?php
	disable_for_anonymous();
	requirePHPLib('form');
	requirePHPLib('judger');
	
	if (!validateUInt($_GET['id']) || !($submission = queryCustomSubmission($_GET['id']))) {
		become404Page();
	}
	$submission_result = json_decode($submission['result'], true);	
	$problem = queryProblemBrief($submission['problem_id']);

	if ($submission_result["time"] != 0 || $submission_result["memory"] != 0) {
		$submission["score"] = 100;
	}

	$submission["used_time"] = $submission_result["time"];
	$submission["used_memory"] = $submission_result["memory"];

	if (!isCustomSubmissionVisibleToUser($submission, $problem, Auth::user())) {
		become403Page();
	}
	
	$out_status = explode(', ', $submission['status'])[0];
	
	if (isSuperUser(Auth::user())) {
		$delete_form = new UOJForm('delete');
		$delete_form->handle = function() {
			global $submission;
			$content = json_decode($submission['content'], true);
			unlink(UOJContext::storagePath().$content['file_name']);
			DB::delete("delete from custom_test_submissions where id = {$submission['id']}");
		};
		$delete_form->submit_button_config['class_str'] = 'btn btn-danger';
		$delete_form->submit_button_config['text'] = '删除此提交记录';
		$delete_form->submit_button_config['align'] = 'right';
		$delete_form->submit_button_config['smart_confirm'] = '';
		$delete_form->succ_href = "/super-manage";
		$delete_form->runAtServer();
	}
	
	$should_show_details = true;
	$should_show_details_to_me = true;
	$should_show_all_details = true;

	if ($should_show_all_details) {
		$styler = new CustomTestSubmissionDetailsStyler();
	}
?>
<?php 
	$REQUIRE_LIB['shjs'] = "";
?>
<?php echoUOJPageHeader(UOJLocale::get('problems::submission').' #'.$submission['id']) ?>
<?php echoSubmissionsListOnlyOne($submission, array('result_hidden' => true, 'language_hidden' => true, 'file_size_hidden' => true), Auth::user()) ?>
<?php echoSubmissionContent($submission, getProblemSubmissionRequirement($problem)) ?>

<?php if ($should_show_all_details): ?>
	<div class="card border-info">
		<div class="card-header bg-info">
			<h4 class="card-title"><?= UOJLocale::get('details') ?></h4>
		</div>
		<div class="card-body">
			<?php echoJudgementDetails($submission_result['details'], $styler, 'details') ?>
			<?php if ($should_show_details_to_me): ?>
				<?php if (isset($submission_result['final_result'])): ?>
					<hr />
					<?php echoSubmissionDetails($submission_result['final_result']['details'], 'final_details') ?>
				<?php endif ?>
				<?php if ($styler->fade_all_details): ?>
					<hr />
					<?php echoSubmissionDetails($submission_result['details'], 'final_details') ?>
				<?php endif ?>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>

<?php if (isset($delete_form)): ?>
	<div class="top-buffer-sm">
		<?php $delete_form->printHTML() ?>
	</div>
<?php endif ?>
<?php echoUOJPageFooter() ?>
