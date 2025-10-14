<?php
/**
 * Admin Pagination Element
 * 
 * Reusable pagination component for admin index pages
 * 
 * @var \App\View\AppView $this
 * @var string $itemType The type of items being paginated (e.g., 'users', 'departments', 'modalities')
 */

$itemType = $itemType ?? 'items';
$singularType = rtrim($itemType, 's'); // Remove 's' for singular form
?>
<div class="card-footer bg-light">
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted">
            <?php echo $this->Paginator->counter(__('Showing {{start}} to {{end}} of {{count}} ' . $itemType)) ?>
        </div>
        <nav aria-label="<?php echo ucfirst($itemType) ?> pagination">
            <ul class="pagination pagination-sm mb-0">
                <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', [
                    'escape' => false,
                    'class' => 'page-link',
                    'title' => 'First page'
                ]) ?>
                <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i>', [
                    'escape' => false,
                    'class' => 'page-link',
                    'title' => 'Previous page'
                ]) ?>
                <?php echo $this->Paginator->numbers([
                    'class' => 'page-link'
                ]) ?>
                <?php echo $this->Paginator->next('<i class="fas fa-angle-right"></i>', [
                    'escape' => false,
                    'class' => 'page-link',
                    'title' => 'Next page'
                ]) ?>
                <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', [
                    'escape' => false,
                    'class' => 'page-link',
                    'title' => 'Last page'
                ]) ?>
            </ul>
        </nav>
    </div>
</div>