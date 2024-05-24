
<?php

$toast_status = $_SESSION["toast_status"];
$toast_msg = $_SESSION["toast_msg"];
unset($_SESSION["toast_status"]);
unset($_SESSION["toast_msg"]);

if(ISSET($toast_status)) : ?>
    <div class="d-flex justify-content-end mb-2" style="position: absolute; top: 100px; right: 40px; z-index: 999;">
        <div class="toast">
            <div class="toast-header bg-<?= $toast_status == 'Success' ? 'success' : 'danger' ?> text-white">
                <?php if($toast_status == 'Success') : ?>
                    <i class="fas fa-check-circle me-2"></i> 
                <?php else: ?>
                    <i class="fas fa-times-circle me-2"></i> 
                <?php endif; ?>
                <?= $toast_status  ?>
            </div>
            <div class="toast-body">
                <?= ISSET($toast_msg) ? $toast_msg : ''  ?>
            </div>
        </div>
    </div>    
<?php endif; ?>

