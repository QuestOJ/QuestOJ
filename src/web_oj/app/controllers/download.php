<?php
	disable_for_anonymous();
	requirePHPLib('judger');
	$disposition = "attachment";

	switch ($_GET['type']) {
		case 'problem':
			if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
				become404Page();
			}
			
			$visible = isProblemVisibleToUser($problem, Auth::user());
			if (!$visible && Auth::check()) {
				$result = DB::query("select contest_id from contests_problems where problem_id = {$_GET['id']}");
				while (list($contest_id) = DB::fetch($result, MYSQLI_NUM)) {
					$contest = queryContest($contest_id);
					genMoreContestInfo($contest);
					if ($contest['cur_progress'] != CONTEST_NOT_STARTED && hasRegistered(Auth::user(), $contest) && queryContestProblemRank($contest, $problem)) {
						$visible = true;
					}
				}
			}
			if (!$visible) {
				become404Page();
			}

			$id = $_GET['id'];
			
	
			$file_name = "/var/uoj_data/$id/download.zip";
			$download_name = "problem_$id.zip";
			
			break;
		case 'testdata':
                        if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
                                become404Page();
                        }
       			if (!hasProblemPermission(Auth::user(), $problem)) {
                		become403Page();
        		}

                        $id = $_GET['id'];

                        
                        $file_name = "/var/uoj_data/$id.zip";
                        $download_name = "testdata_$id.zip";
                        
						break;
		case 'statement':
			if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
				become404Page();
			}
			if (!hasProblemPermission(Auth::user(), $problem)) {
				become403Page();
			}

			$id = $_GET['id'];

			$file_name = "/var/uoj_data/$id/statement.pdf";
			$download_name = "statement_$id.pdf";
			$disposition = "inline";
			break;					
		case 'testlib.h':
			$file_name = "/home/local_main_judger/judge_client/uoj_judger/include/testlib.h";
			$download_name = "testlib.h";
			break;
		default:
			become404Page();
	}
	
	$finfo = finfo_open(FILEINFO_MIME);
	$mimetype = finfo_file($finfo, $file_name);
	if ($mimetype === false) {
		become404Page();
	}
	finfo_close($finfo);
	
	header("X-Sendfile: $file_name");
	header("Content-type: $mimetype");
	header("Content-Disposition: $disposition; filename=$download_name");
?>
