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
<div class="alert alert-success">
    <h2>Поздравляем</h2>
    <p>Ваши файлы в количестве <?php echo count($this->diffs)?> были восстановлены</p>
    <p>Через <span id="update_interval">10</span> секунд, вы будете переброшены на стандартное обновление Joomla ...</p>
</div>
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