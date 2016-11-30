(function ($) {
    'use strict';
    $(function () {
        if ($('.compare_progress').length) {
            $.get('index.php?option=com_safe_update&task=run.compareandsave', function () {
                location.href = 'index.php?option=com_safe_update&task=run.comparecache&returnurl=' + $('.compare_progress').data('returnurl');
            });
        }
    });
} (jQuery))