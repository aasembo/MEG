<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 */
use Cake\Core\Configure;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.php');

    $this->start('file');
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>
<h2><?php echo h($message) ?></h2>
<p class="error">
    <strong><?php echo __d('cake', 'Error') ?>: </strong>
    <?php echo __d('cake', 'The requested address {0} was not found on this server.', "<strong>'{$url}'</strong>") ?>
</p>
