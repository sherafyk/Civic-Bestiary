<?php if (!defined('ABSPATH')) { exit; } ?>
<div <?php echo $shell_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> >
    <?php echo $notice_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php echo $progress_html ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    <?php if ('after_form' === ($report_position ?? 'before_form')) : ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo $refine_intro_html ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo $dashboard_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php else : ?>
        <?php echo $dashboard_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo $refine_intro_html ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php endif; ?>

    <?php if (empty($form_html) && empty($refine_intro_html) && !empty($empty_message)) : ?>
        <div class="acb-panel acb-panel--empty"><p><?php echo esc_html($empty_message); ?></p></div>
    <?php endif; ?>
</div>
