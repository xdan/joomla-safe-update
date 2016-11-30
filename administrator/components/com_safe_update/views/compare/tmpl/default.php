<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Safe_Update
 * @author     Valeriy <chupurnov@gmail.com>
 * @copyright  2016 Valeriy Chupurnov XDSoft.net
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
?>
<div class="safe-update-root">
<?php
if ($this->returnurl) { ?>
    <div class="compare_progress_nextstep" data-returnurl="<?php echo $this->returnurl?>">
        <img src="<?php echo juri::root() . '/administrator/components/com_safe_update/assets/images/progress.gif'?>" alt="">
        <h2>Через <span id="update_interval">10</span> секунд, вы будете переброшены на обновление Joomla ...</h2>
        <script>
            setInterval(function () {
                var interval = parseInt(jQuery('#update_interval').text(), 10);
                interval--;
                jQuery('#update_interval').text(interval);
                if (interval <=0) {                    
                    location.href="<?php echo base64_decode($this->returnurl)?>";
                }
            }, 1000);
        </script>
    </div>
<? }

if (!count($this->diffs)) { ?>
    <div class="alert alert-error">Не найдено ни одного измененного файла</div>
<?php } else {
    ?>
        <table class="table">
            <tr>
                <th>Изменено:</th>
                <td><?php echo count($this->diffs)?> файлов</td>
            </tr>
            <tr>
                <th>Архив:</th>
                <td><a href="<?php echo juri::root() . $this->file;?>"><?php echo $this->file;?></a></td>
            </tr>
        </table>
    <?php
    foreach ($this->diffs as $path => $diff) { ?>
       <h2><?php echo $path?></h2>
       <hr>
       <?php echo Diff::toTable($diff);?>
    <?php }
}
?>
</div>