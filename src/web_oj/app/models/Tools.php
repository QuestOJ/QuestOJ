<?php

requirePHPLib('data');
requirePHPLib('rating');

class Tools {
    private static function lockService() {
        fclose(fopen(UOJContext::documentRoot()."/app/.lock", "a"));
    }

    private static function unlockService() {
        unlink(UOJContext::documentRoot()."/app/.lock");
    }
	
    private static function moveBack($id) {
        $oldID = $id;
        $newID = $id + 1;

        DB::update("update best_ac_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update click_zans set target_id = '{$newID}' where target_id = '{$oldID}' and type='P'");
        DB::update("update contests_problems set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update contests_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update custom_test_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update hacks set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_auth set pid = '{$newID}' where pid = '{$oldID}'");
        DB::update("update problems_contents set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_permissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems_tags set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");

        rename("/var/uoj_data/upload/{$oldID}", "/var/uoj_data/upload/{$newID}");
        rename("/var/uoj_data/{$oldID}", "/var/uoj_data/{$newID}");
        rename("/var/uoj_data/{$oldID}.zip", "/var/uoj_data/{$newID}.zip");
    }

    private static function moveFront($id) {
        $oldID = $id;
        $newID = $id - 1;

        DB::update("update best_ac_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update click_zans set target_id = '{$newID}' where target_id = '{$oldID}' and type='P'");
        DB::update("update contests_problems set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update contests_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update custom_test_submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update hacks set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_auth set pid = '{$newID}' where pid = '{$oldID}'");
        DB::update("update problems_contents set id = '{$newID}' where id = '{$oldID}'");
        DB::update("update problems_permissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update problems_tags set problem_id = '{$newID}' where problem_id = '{$oldID}'");
        DB::update("update submissions set problem_id = '{$newID}' where problem_id = '{$oldID}'");

        rename("/var/uoj_data/upload/{$oldID}", "/var/uoj_data/upload/{$newID}");
        rename("/var/uoj_data/{$oldID}", "/var/uoj_data/{$newID}");
        rename("/var/uoj_data/{$oldID}.zip", "/var/uoj_data/{$newID}.zip");
    }

    private static function deleteProblem($id) {
        dataClearProblemData($id);
        DB::query("delete from problems where id ='{$id}'");
        DB::query("delete from problems_contents where id ='{$id}'");
        DB::query("delete from best_ac_submissions where problem_id = '{$id}'");
        DB::query("delete from click_zans set target_id = '{$newID}' where target_id = '{$id}' and type='P'");
        DB::query("delete from contests_problems where problem_id = '{$id}'");
        DB::query("delete from contests_submissions where problem_id = '{$id}'");
        DB::query("delete from custom_test_submissions where problem_id = '{$id}'");
        DB::query("delete from hacks where problem_id = '{$id}'");
        DB::query("delete from problems where id = '{$id}'");
        DB::query("delete from problems_auth where pid = '{$id}'");
        DB::query("delete from problems_contents where id = '{$id}'");
        DB::query("delete from problems_permissions where problem_id = '{$id}'");
        DB::query("delete from problems_tags where problem_id = '{$id}'");
        DB::query("delete from submissions where problem_id = '{$id}'");
    }

    private static function newProblem($id) {
        DB::query("insert into problems (id, title, is_hidden, submission_requirement) values ('{$id}', 'New Problem', 1, '{}')");
        DB::query("insert into problems_contents (id, statement, statement_md) values ($id, '', '')");
        dataNewProblem($id);
    }

    public static function insert($target) {
        Tools::lockService();

        $oldCnt = DB::num_rows("select id from problems");
        $newCnt = $oldCnt + 1;
        $AI = $newCnt + 1;

        print("Total problem {$oldCnt}\n");
        
        for ($id=$oldCnt; $id>=$target; $id--){
            $oldID = $id;
            $newID = $id + 1;
            print("Move problem {$oldID} to {$newID}\n");
            Tools::moveBack($id);
        }

        print("Insert problem {$target}\n");	
        Tools::newProblem($target);
        DB::update("alter table problems AUTO_INCREMENT={$AI}");

        Tools::unlockService();
    }

    public static function delete($target) {
        Tools::lockService();

        $oldCnt = DB::num_rows("select id from problems");
        $newCnt = $oldCnt - 1;
        $AI = $newCnt + 1;

        print("Total problem {$oldCnt}\n");

        print("Delete problem {$target}\n");	
        Tools::deleteProblem($target);

        for ($id=$target+1; $id<=$oldCnt; $id++){
            $oldID = $id;
            $newID = $id - 1;
            print("Move problem {$oldID} to {$newID}\n");
            Tools::moveFront($id);
        }

        DB::update("alter table problems AUTO_INCREMENT={$AI}");

        Tools::unlockService();
    }

    public static function calc($target) {
        print("Contest {$target}\n");

        $contest = queryContest($target);
        print("Contest name: {$contest["name"]}\n");

        $contests_registrants = DB::selectAll("select * from contests_registrants where contest_id = {$contest["id"]}");

        for ($i = 0; $i < count($contests_registrants); $i++) {
            $user = queryUser($contests_registrants[$i]["username"]);
            $performance = $user["performance"];
            $rating = $user["rating"];
            
            DB::update("update contests_registrants SET average_performance = {$performance}, user_rating = {$rating} where username = '{$contests_registrants[$i]["username"]}' and contest_id = {$contests_registrants[$i]["contest_id"]}");

            print("User {$contests_registrants[$i]["username"]} performance change to {$performance}\n");
        } 

        $contest_data = queryContestData($contest);
        genMoreContestInfo($contest);

        calcStandings($contest, $contest_data, $score, $standings, true);
        $rating = calcRating($standings, $contest);

        $rank = 1;
        foreach ($standings as $particular) {
            $index = $rank - 1;
            print("User {$particular[2][0]} rating {$rating[$index]}\n");
            DB::update($sql="update user_info SET rating = {$rating[$index]} where username = '{$particular[2][0]}'");
            $rank += 1;
        }
    }

    public static function calcAll() {
        Tools::lockService();

        DB::query("TRUNCATE `contests_history`");
        DB::query("UPDATE `user_info` SET rating = 0, performance = -1");

        $contests = DB::selectAll("select id from contests where status = 'finished' order by start_time, id");
        $userOld = DB::selectAll("select username, rating from user_info");

        for ($i = 0; $i < count($contests); $i++) {
            Tools::calc($contests[$i]["id"]);
            sleep(1.5);
        }

        $userNew = DB::selectAll("select username, rating from user_info");

        for ($i = 0; $i < count($userOld); $i++) {
            $change = $userNew[$i]['rating'] - $userOld[$i]['rating'];
            $user_link = getUserLink($userNew[$i]['username']);

            if ($change != 0) {
                $tail = '<strong style="color:red">' . ($change > 0 ? '+' : '') . $change . '</strong>';
                $content = <<<EOD
<p>${user_link} 您好：</p>
<p class="indent2">经过重新计算后您的Rating变化为${tail}，当前Rating为 <strong style="color:red">{$userNew[$i]['rating']}</strong>。</p>
EOD;
            } else {
                $content = <<<EOD
<p>${user_link} 您好：</p>
<p class="indent2">经过重新计算后您的Rating没有变化。当前Rating为 <strong style="color:red">{$userNew[$i]['rating']}</strong>。</p>
EOD;
            }
            sendSystemMsg($userNew[$i]['username'], 'Rating变化通知', $content);
        }

    Tools::unlockService();
    }
}