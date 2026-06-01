<?php if (!defined('ABSPATH')) { exit; } ?>
<div <?php echo $shell_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
    <?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php echo $dashboard_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php if ($form_html) : ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php elseif ($empty_message) : ?>
        <div class="acb-panel acb-panel--empty"><p><?php echo esc_html($empty_message); ?></p></div>
    <?php endif; ?>
</div>
