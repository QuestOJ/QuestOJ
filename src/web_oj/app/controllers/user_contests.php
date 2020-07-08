<?php
        $username = $_GET["username"];

        if (!validateUsername($username) || !($user = queryUser($username))) {
                become404Page();
        }

        requirePHPLib('form');
	requirePHPLib('rating');

        $rating = $user["rating"];

        function echoContest($contest, $id) {
                global $user, $rating;

                $diff = $rating - $contest["user_rating"];
                if ($diff > 0) {
                        $diff = '<span style="color:green;font-weight:bold;">+'.$diff.'</span';
                } else if ($diff < 0) {
                        $diff = '<span style="color:gray;font-weight:bold;">'.$diff.'</span';
                } else {
                        $diff = '<span style="color:black;font-weight:bold;">'.$diff.'</span';
                }

                echo '<tr class="text-center">';
                echo '<td>#'.$id.'</td>';
                echo '<td><a href="/contest/'.$contest["contest_id"].'">'.queryContest($contest["contest_id"])["name"].'</a></td>';
                echo '<td><a href="/contest/'.$contest["contest_id"].'/standings">'.$contest["rank"].'</a></td>';
                echo '<td>'.$contest["performance"].'</td>';
                echo '<td>'.$diff.'</td>';
				echo '<td>'.$rating.'</td>';
				if (!(calcRatingGroup($rating) == calcRatingGroup($contest["user_rating"]))) {
					echo '<td><span class="uoj-username" data-rating="'.$contest["user_rating"].'" data-link="0">'.$user["username"].'</span> → <span class="uoj-username" data-rating="'.$rating.'" data-link="0">'.$user["username"].'</span></td>';
				} else {
                	echo '<td></td>';
				}
				echo '</tr>';

                $rating = $contest["user_rating"];
        }

        $header = '<tr>';
        $header .= '<th class="text-center" style="width:5em;">ID</th>';
        $header .= '<th class="text-center">'.UOJLocale::get('contests').'</th>';
        $header .= '<th class="text-center">'.UOJLocale::get('rank').'</th>';
        $header .= '<th class="text-center">'.UOJLocale::get('performance').'</th>';
        $header .= '<th class="text-center">'.UOJLocale::get('rating changes').'</th>';
        $header .= '<th class="text-center">'.UOJLocale::get('new rating').'</th>';
        $header .= '<th></th>';
        $header .= '</tr>';

        $pag_config['col_names'] = array('*');
        $pag_config['table_name'] = "contests_history left join contests_registrants on contests_registrants.username='{$user['username']}' and contests_registrants.contest_id = contests_history.contest_id";
        $pag_config['cond'] = "contests_history.username = '{$user['username']}'";
        $pag_config['echo_full'] = '';
        $pag_config['tail'] = "order by id desc";
        $pag = new Paginator($pag_config);

        $div_classes = array('table-responsive');
        $table_classes = array('table', 'table-bordered', 'table-hover', 'table-striped');
?>
<?php echoUOJPageHeader(UOJLocale::get('contests history')) ?>
<div class="top-buffer-sm"></div>
<?php
        echo '<div class="', join($div_classes, ' '), '">';
        echo '<table class="', join($table_classes, ' '), '">';
        echo '<thead>';
        echo $header;
        echo '</thead>';
        echo '<tbody>';

        $id = DB::selectCount("select count(*) from contests_history where username='{$user["username"]}'") + 1;
        foreach ($pag->get() as $idx => $row) {
                $id -= 1;
                echoContest($row, $id);
                echo "\n";
        }
        if ($pag->isEmpty()) {
                echo '<tr><td class="text-center" colspan="233">'.UOJLocale::get('none').'</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
?>
<?php echoUOJPageFooter() ?>