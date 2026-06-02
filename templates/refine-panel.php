<?php if (!defined('ABSPATH')) { exit; } ?>
<section class="acb-refine acb-panel">
    <div class="acb-refine__copy">
        <p class="acb-eyebrow"><?php esc_html_e('Optional refinement', 'american-civic-bestiary'); ?></p>
        <h2><?php esc_html_e('Refine my profile with more questions', 'american-civic-bestiary'); ?></h2>
        <p class="acb-lead">
            <?php
            echo esc_html(sprintf(
                _n('%d unanswered scenario remains. Answer it only if you want a sharper profile.', '%d unanswered scenarios remain. Answer them only if you want a sharper profile.', (int) $question_count, 'american-civic-bestiary'),
                (int) $question_count
            ));
            ?>
        </p>
    </div>

    <?php if ('automatic' === ($refine_mode ?? 'button')) : ?>
        <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    <?php else : ?>
        <details class="acb-refine__details">
            <summary class="acb-button" role="button"><?php esc_html_e('Show refinement questions', 'american-civic-bestiary'); ?></summary>
            <?php echo $form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </details>
    <?php endif; ?>
</section>
