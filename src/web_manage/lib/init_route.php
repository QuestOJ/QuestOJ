<?php

    /**
     * 执行库：地址转发 (lib/lib_controller.php)
     */

    if(!defined("load")){
        header("Location:/403");
        exit;
    }

    ob_clean();
    ob_start();

    $html = new html();

    $html->route("/", "default", "首页", 0);

    $html->route("/403", "403", "403", 0);
    $html->route("/404", "404", "404", 0);
    $html->route("/login", "login", "登录", 0);
    $html->route("/loginout", "loginout", NULL, 1);

    $html->route("/manage/status", "manage_status", "运行状态", 0);
    $html->route("/manage/log", "manage_log", "系统日志", 0);
    $html->route("/manage/log/page/(\d+)", "manage_log", "系统日志", 0);
    $html->route("/manage/log/info/(\d+)", "manage_log_info", "日志详情", 0);
    $html->route("/manage/setting", "manage_setting", "系统设置", 0);
    $html->route("/manage/setting/submit", "manage_setting", NULL, 1);
    
    $html->route("/service/log", "service_log", "服务日志", 0);
    $html->route("/service/log/page/(\d+)", "service_log", "服务日志", 0);
    $html->route("/service/log/info.(\d+)", "service_log_info", "服务日志详情", 0);
    $html->route("/service/log/detail", "service_log_detail", NULL, 1);

    $html->route("/user/list", "user_list", "用户列表", 0);
    $html->route("/user/list/page/(\d+)", "user_list", "用户列表", 0);
    $html->route("/user/group", "user_group", "用户组列表", 0);
    $html->route("/user/group/page/(\d+)", "user_group", "用户组列表", 0);

    $html->route("/blog/list", "blog_list", "博客列表", 0);
    $html->route("/blog/list/page/(\d+)", "blog_list", "博客列表", 0);
    $html->route("/blog/announcement", "blog_announcement", "公告列表", 0);
    $html->route("/blog/announcement/page/(\d+)", "blog_announcement", "公告列表", 0);
    $html->route("/blog/contests", "blog_contests", "比赛资料", 0);
    $html->route("/blog/contests/page/(\d+)", "blog_contests", "比赛资料", 0);

    $html->route("/user/edit", "user", NULL, 1);
    $html->route("/user/group/edit", "user_group", NULL, 1);
    $html->route("/blog/edit", "blog", NULL, 1);
    $html->route("/blog/announcement/edit", "blog_announcement", NULL, 1);
    $html->route("/blog/contests/edit", "blog_contests", NULL, 1);

    $html->route("/submission/fail", "submission_fail", "失败的评测", 0);
    $html->route("/submission/fail/page/(\d+)", "submission_fail", "失败的评测", 0);

    $html->route("/submission/custom", "submission_custom", "自定义评测", 0);
    $html->route("/submission/custom/page/(\d+)", "submission_custom", "自定义评测", 0);

    $html->checkRoute();
    $html->printHTML();

    ob_flush();
    ob_end_flush();
?>