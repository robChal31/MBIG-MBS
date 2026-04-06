<?php
session_start();
include 'db_con.php';
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$id     = $_POST['id'] ?? 0;
$action = $_POST['action'] ?? '';
$mpartner = [];
$mp_sql = "SELECT mpu.*
            FROM mp_users AS mpu
            WHERE mpu.id = $id";
$draft_exec = mysqli_query($conn, $mp_sql);
if (mysqli_num_rows($draft_exec) > 0) {
  $mpartner = mysqli_fetch_all($draft_exec, MYSQLI_ASSOC);    
}

$mpartner = $mpartner[0] ?? [];

$email = $mpartner['email'] ?? '';
$name = $mpartner['name'] ?? '';

// Determine dialog content based on action
$isEmailAction = ($action == 'emailAct');
$title = $isEmailAction ? 'Resend Welcome Email' : 'Create Partner Account';
$icon = $isEmailAction ? '📧' : '👤';
$description = $isEmailAction 
    ? 'This user hasn\'t received their welcome email yet.' 
    : 'This user hasn\'t created an account on Mentari Partner platform yet.';
$buttonText = $isEmailAction ? 'Send Email Now' : 'Create Account Now';
$buttonIcon = $isEmailAction ? '✉️' : '➕';
?>


    <style>
        .modal-container {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .modal-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            box-shadow: 0 8px 20px rgba(102,126,234,0.25);
        }
        
        .modal-title {
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            margin: 0 0 12px 0;
            color: #1a202c;
        }
        
        .modal-description {
            text-align: center;
            color: #4a5568;
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 8px 0;
        }
        
        .modal-info {
            background: #f7fafc;
            border-radius: 12px;
            padding: 16px;
            margin: 20px 0;
            border-left: 3px solid #667eea;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #2d3748;
            min-width: 45px;
        }
        
        .info-value {
            color: #4a5568;
            word-break: break-all;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .btnx {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-family: inherit;
        }
        
        .btnx-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btnx-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        
        .btnx-primaryx {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(102,126,234,0.3);
        }
        
        .btnx-primaryx:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.4);
        }
        
        .btnx-primaryx:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .warning-text {
            font-size: 12px;
            color: #e53e3e;
            text-align: center;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
    </style>

<div class="modal-container">
    <form action="save-mpartner-act.php" method="POST" enctype="multipart/form-data" id="form_input">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
        <input type="hidden" name="action" value="<?= htmlspecialchars($action) ?>">
        
        <!-- Icon -->
        <div class="modal-icon">
            <?= $icon ?>
        </div>
        
        <!-- Title -->
        <h3 class="modal-title"><?= htmlspecialchars($title) ?></h3>
        
        <!-- Description -->
        <p class="modal-description"><?= htmlspecialchars($description) ?></p>
        
        <!-- Info Box (if user data available) -->
        <?php if ($name || $email): ?>
            <div class="modal-info">
                <?php if ($name): ?>
                <div class="info-row">
                    <span class="info-label">👤 Name:</span>
                    <span class="info-value"><?= htmlspecialchars($name) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($email): ?>
                <div class="info-row">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value"><?= htmlspecialchars($email) ?></span>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Action Message -->
        <p class="modal-description" style="font-weight: 500; margin-top: 16px;">
            <?= $isEmailAction ? 'Click send to resend the welcome email immediately.' : 'This will create an account to Mentari Partner.' ?>
        </p>
        
        <!-- <?php if (!$isEmailAction): ?>
            <div class="warning-text">
                ⚡ This action cannot be undone
            </div>
        <?php endif; ?> -->
        
        <!-- Buttons -->
        <div class="button-group">
            <button type="button" class="btnx btnx-secondary close">Cancel</button>
            <button type="submit" class="btnx btnx-primaryx" id="submitBtnx">
                <?= $buttonIcon ?> <?= htmlspecialchars($buttonText) ?>
            </button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    
    $('#form_input').on('submit', function(event) {
        event.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: './save-mpartner-act.php', 
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#submitBtnx').prop('disabled', true);
                Swal.fire({
                    title: 'Processing...',
                    html: '<?= $isEmailAction ? "Sending email..." : "Creating account..." ?>',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                
                if(response.status == 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#667eea',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Failed!',
                        text: response.message || 'Something went wrong',
                        icon: 'error',
                        confirmButtonColor: '#667eea'
                    });
                    $('#submitBtnx').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Network error. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#667eea'
                });
                $('#submitBtnx').prop('disabled', false);
            }
        });
    });
    
    // Close button handler
    $('.close').on('click', function() {
        // If using SweetAlert2 modal
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        // If using Bootstrap modal
        $(this).closest('.modal').modal('hide');
    });
});
</script>
</body>
</html>